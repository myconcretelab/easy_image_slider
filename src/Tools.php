<?php

namespace EasyImageSlider;

use Concrete\Core\Controller\Controller as RouteController;
use Concrete\Core\File\EditResponse as FileEditResponse;
use Concrete\Core\File\File;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\Permission\Checker;
use Loader;
use stdClass;

class Tools extends RouteController
{
    public function save()
    {
        $this->file = File::getByID($_REQUEST['fID']);
        $fp = new Checker($this->file);
        if ($fp->canEditFileProperties()) {
            $fv = $this->file->getVersionToModify();

            $value = $_REQUEST['value'];
            switch ($_REQUEST['name']) {
                case 'fvTitle':
                    $fv->updateTitle($value);
                    break;
                case 'fvDescription':
                    $fv->updateDescription($value);
                    break;
                case 'fvTags':
                    $fv->updateTags($value);
                    break;
                default:
                    // Save a attribute
                    $fv->setAttribute($_REQUEST['name'], $value);
            }

            $sr = new FileEditResponse();
            $sr->setFile($this->file);
            $sr->setMessage(t('File updated successfully.'));
            $sr->setAdditionalDataAttribute('value', $value);
            $sr->outputJSON();
        } else {
            throw new \Exception(t('Access Denied.'));
        }
        die();
    }

    public function getFileSetImage()
    {
        // $fs = new SetList()->get();
        // Loader::helper('ajax')->sendResult(
        // die('coucou');
        $fs = FileSet::getByID((int) ($_REQUEST['fsID']));
        if (is_object($fs)) {
            $fsf = $fs->getFiles();
        }
        // print_r($fsf);
        if (count($fsf)) {
            foreach ($fsf as $key => $f) {
                $fd = $this->getFileDetails($f);
                if ($fd) {
                    $files[] = $fd;
                }
            }
            Loader::helper('ajax')->sendResult($files);
        }
    }

    public function getFileThumbnailUrl($f = null)
    {
        if (!$f && $_REQUEST['fID']) {
            $f = File::getByID($_REQUEST['fID']);
        }

        $type = \Concrete\Core\File\Image\Thumbnail\Type\Type::getByHandle('file_manager_detail');
        if ($type != null) {
            return $f->getThumbnailURL($type->getBaseVersion());
        }

        return false;
    }

    public function getFileDetails($f = null)
    {
        if (!$f && $_REQUEST['fID']) {
            $f = File::getByID($_REQUEST['fID']);
        }

        $o = new stdClass();
        if (!is_object($f)) {
            return false;
        }
        $o->urlInline = $this->getFileThumbnailUrl($f);
        $o->title = $f->getTitle();
        $o->description = $f->getDescription();
        $o->fID = $f->getFileID();
        $o->type = $f->getVersionToModify()->getGenericTypeText();
        $o->image_thumbnail_width = $f->getAttribute('image_thumbnail_width') ? $f->getAttribute('image_thumbnail_width') : '';
        $o->image_link = $f->getAttribute('image_link') ? $f->getAttribute('image_link') : '';
        $o->image_link_text = $f->getAttribute('image_link_text') ? $f->getAttribute('image_link_text') : '';
        $o->image_bg_color = $f->getAttribute('image_bg_color') ? $f->getAttribute('image_bg_color') : ''; // '#ffffff';

        return $o;
    }

    public function getFileDetailsJson()
    {
        Loader::helper('ajax')->sendResult($this->getFileDetails());
    }
}
