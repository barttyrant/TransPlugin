<?php

App::uses('TransAppModel', 'Trans.Model');

class Translation extends TransAppModel {

    
    public $name = 'Translation';
 	
 	const STATUS_UNTRANSLATED = 0;
 	const STATSUS_TRANSLATED = 1;
 	const STATUS_IGNORED = 3;
    
    
    public function saveToPOFile($id){
        
    }
    
    
    public function ignore($id){
        $return = $this->updateAll(array(
            'Translation.status' => self::STATUS_IGNORED
        ), array('Translation.id' => $id));
        
        return $return;
    }
    
    public function translate($id, $msgstr = '', $saveToPoFile = false){
        $return = $this->updateAll(array(
            'Translation.status' => self::STATSUS_TRANSLATED,
            'Translation.msgstr' => '"' . $msgstr . '"'
        ), array('Translation.id' => $id));
        
        return $return;        
    }
    
    

}
