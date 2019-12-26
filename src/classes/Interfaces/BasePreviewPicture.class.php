<?php

interface BasePreviewPicture
{
    public function getPreviewPath($type = PictureUtils::PREVIEW_IMAGE_TYPE_SMALL);
    public function getPreviewUrl($type = PictureUtils::PREVIEW_IMAGE_TYPE_SMALL);
}