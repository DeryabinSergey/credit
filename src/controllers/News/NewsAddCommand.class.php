<?

class NewsAddCommand extends AddCommand implements SecurityCommand
{    
    const ERROR_DUPLICATE = 0x0003;
    const ERROR_INTERNAL = 0x0004;
    
    /**
     * @return NewsAddCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
	
	$previewFile = Session::exist('preview-'.get_class($subject)) ? Session::get('preview-'.get_class($subject)) : false;
        $dropPreview = true;
	
        if ($process) {

	    if ($previewFile) {
                $subject->setPreview(ImageType::createByFileName(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$previewFile));
            }
	    
	    try {
                $tr = InnerTransaction::begin($subject->dao());
                $mav = parent::run($subject, $form, $request);
                if ($mav->getView() == EditorController::COMMAND_SUCCEEDED) {
                    if ($previewFile) {
                        $list = PictureUtils::getAvailablePreviewTypes($subject);
                        foreach ($list as $type) {
                            if (PictureUtils::checkDir($subject->getPreviewPath($type)) !== true)
                                throw new Exception("Can`t create preview directory {$subject->getPreviewPath($type)}");
                            PictureUtils::resize(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$previewFile, PictureUtils::getPreviewResizeSizes($subject, $type), $subject->getPreviewPath($type), true);
                        }
                    }
                }
                $tr->commit();
            } catch(DuplicateObjectException $e) {
		$tr->rollback();
                $form->markCustom('id', self::ERROR_DUPLICATE);
            } catch(Exception $e) {
                $tr->rollback();
                error_log("Ошибка при добавлении новости: {$e->getMessage()}\n{$e->getTraceAsString()}");
                $form->markCustom('id', self::ERROR_INTERNAL);
                $list = PictureUtils::getAvailablePreviewTypes($subject);
                if (is_int($subject->getId()) && intval($subject->getId()) == $subject->getId()) {
                    foreach ($list as $type) {
                        if (file_exists($subject->getPreviewPath($type))) unlink($subject->getPreviewPath($type));
                    }
                }
                if ($mav->getView() == EditorController::COMMAND_SUCCEEDED) {
                    $mav->setView(EditorController::COMMAND_FAILED);
                }
            }
        }
	    
	$dropPreview = $mav->getView() == EditorController::COMMAND_SUCCEEDED;
	    
	if ($mav->getView() != BaseEditor::COMMAND_SUCCEEDED) {

	    $mav->
		getModel()->
		    set('preview', Session::exist('preview-'.get_class($subject)) ? UPLOAD_URL.IMAGE_PATH_TEMP.Session::get('preview-'.get_class($subject)).'?'.time() : false);

	}

	if ($dropPreview && $previewFile) {
	    if (file_exists(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$previewFile)) unlink(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$previewFile);
	    if (file_exists(UPLOAD_PATH.IMAGE_PATH_TEMP.$previewFile)) unlink(UPLOAD_PATH.IMAGE_PATH_TEMP.$previewFile);
	    Session::drop('preview-'.get_class($subject));
	}

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            drop('createdDate')->
            drop('sid')->
            drop('type')->
	    drop('preview')->
            get('title')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars());
        
        $form->
            get('description')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars());
	
	$form->
            get('text')->
                addImportFilter(
                    FilterChain::create()->
                        add(Filter::pcre()->setExpression("/<p[^>]*>(&nbsp;|\s|)*<\/p>(\\r?\\n)?/iu", ""))->
                        add(Filter::pcre()->setExpression("/<(div|span|font|h1|h2|h3|h4|h5|h6)>(&nbsp;|\s|)*<\/\\1>(\\r?\\n)?/iu", ""))->
                        add(Filter::trim())
                );

        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::ADD_ACTION, AclContext::NEWS_ID);
    }

}