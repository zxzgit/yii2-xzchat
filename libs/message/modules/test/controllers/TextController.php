<?php
/**
 * Created by zxzTool.
 * User: zxz
 * Datetime: 2018/11/15 18:23
 */

namespace xzchat\libs\message\modules\test\controllers;

use xzchat\libs\message\controllers\AbstractController;

class TextController extends AbstractController {
    
    public function run() {
        return $this->pushMsg(["33222000-modules/admin/controllers/text", "world"]);
    }
}