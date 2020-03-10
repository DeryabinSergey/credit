<?

class CreditRequestCreditorOfferAcceptCommand extends MakeCommand implements SecurityCommand, EditorCommand
{
    const ERROR_EXTERNAL = 0x0003;

    /**
     * @return CreditRequestCreditorOfferRejectCommand
     */
    public static function create() { return new self; }
		
    /**
     * @return ModelAndView
    **/
    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = parent::run($subject, $form, $request);
        
        if ($mav->getView() == EditorController::COMMAND_SUCCEEDED) {

            /**
             * Отправка уведомлений пользователям с правами на публикацию заявлений на кредит
             */
            $groupsIds = 
                ArrayUtils::convertToPlainList(
                    Criteria::create(AclGroupRight::dao())->
                        setDistinct()->
                        addProjection(Projection::property('group'))->
                        add(Expression::eq('right.context', AclContext::CREDIT_REQUEST_ID))->
                        add(Expression::eq('right.action.action', AclAction::PUBLISH_ACTION))->
                        getCustomList(), 'group_id'
                );
            if ($groupsIds) {
                $users = 
                    Criteria::create(User::dao())->
                        add(Expression::in('group', $groupsIds))->
                        getList();

                foreach($users as $user) {
                    if ($user->getEmail()) {
                        MimeMailSender::create('Заемщик принял кредитное предложение - назначить встречу', 'creditRequestCreditorOfferAcceptHtml', 'creditRequestCreditorOfferAcceptText')->
                            setTo($user->getEmail(), $user->getName())->
                            set('offer', $subject)->
                            set('user', $user)->
                            send();
                    }
                }
            }            
        }
        
        return $mav;
    }
    
    protected function daoMethod() { return 'accept'; }

    public function checkPermissions(Form $form)
    {
        return $form->getValue('id')->checkPermissions(AclAction::ACCEPT_ACTION);
    }
}