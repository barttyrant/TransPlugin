<?php

class TranslationsController extends TransAppController {

    public $uses = array('Trans.Translation');

    function index() {
        
        $data = array();
        
        $this->paginate = array(
            'conditions' => array(
            ));
        
        if(!empty($this->request->data)){
            $data = $this->request->data;
        }

        if (!empty($data['Translation']['msgid'])) {
            $data['Translation']['msgid'] = trim($data['Translation']['msgid']);
            if ($data['Translation']['in_translation']) {
                $this->paginate['conditions'][] = 'msgstr LIKE "%' . $data['Translation']['msgid'] . '%"';
            } else {
                $this->paginate['conditions'][] = 'msgid LIKE "%' . $data['Translation']['msgid'] . '%"';
            }
            if ($status != Translation::IGNORED) {
                $this->paginate['conditions']['status <>'] = $status;
            }
        } else {
            $this->paginate['conditions']['status'] = $status;
            $toTranslate = $this->paginate('Translation');

            $this->set(compact('toTranslate', 'status'));
        }
    }

    function translate($id = null) {
        $this->layout = false;
        $this->autoRender = false;
        $l10n = new L10n();
        
        $data = array();
        
        if(!empty($this->request->data)){
            $data = $this->request->data;
        }

        $id = !empty($this->params['named']['id']) ? $this->params['named']['id'] : null;

        if (is_null($id)) {
            throw new Exception('translation id is empty');
        }

        $translation = $this->Translation->find('first', array(
            'conditions' => array(
                'Translation.id' => $id
            )
        ));

        $result = array('ok' => false, 'msg' => '');

        try {
            if (empty($result))
                throw new Exception('There is no such translations');

            if (empty($data)) {
                throw new Exception('No translation send');
            }
            $data['Translation']['msgstr'] = trim($data['Translation']['msgstr']);

            if (empty($data['Translation']['msgstr'])) {
                throw new Exception('Empty translation send');
            }

            $data['Translation']['msgstr'] = nl2br($data['Translation']['msgstr']);

            $poPath = $this->_getLocalPath();
            if (!file_exists($poPath)) {
                throw new Exception('Empty translation send');
            }

            $f_arr = file($poPath);

            if ($f_arr === false) {
                throw new Exception('Unable to read file, check permissions');
            }

            $changed = false;
            $reg = 'msgid "' . $translation['Translation']['msgid'] . '"';
            $lno = 0;
            $needSave = false;
            foreach ($f_arr as $line) {
                $line = trim($line);
                if (strpos($line, $reg) === 0) {
                    $changed = true;
                    $needSave = true;
                    $f_arr[$lno + 1] = 'msgstr "' . $data['Translation']['msgstr'] . '"' . "\n";
                    break;
                }
                $lno++;
            }

            if (!$changed) {//nie znalazlo w istniejacym, wiec dodaje nowa fraze na koniec pliku
                $refs = unserialize($translation['Translation']['references']);
                foreach ($refs as $ref) {
                    $f_arr[] = '#: ' . $ref . "\n";
                }
                $f_arr[] = 'msgid "' . $translation['Translation']['msgid'] . '"' . "\n";
                $f_arr[] = 'msgstr "' . $data['Translation']['msgstr'] . '"' . "\n\n";
                $needSave = true;
            }
            if ($needSave) {
                if (file_put_contents($poPath, $f_arr) === false) {
                    throw new Exception('Unable to save new file. Check permissions');
                };
                $updateData = array(
                    'status' => Translation::TRANSLATED,
                    'msgstr' => '\'' . $data['Translation']['msgstr'] . '\'',
                    'modified' => '\'' . date('Y-m-d H:i:s') . '\'',
                );
                if ($this->Translation->updateAll($updateData, array('id' => $id))) {
                    $result['ok'] = true;
                    Cache::clear(false, 'core');
                } else {
                    $result['ok'] = false;
                    $result['msg'] = 'Unable to save into db';
                }
            }
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }
        echo json_encode($result);

        Configure::write('debug', 0);
    }

    /**
     * wyswietlanie przetlumaczonych fraz
     */
    function ignore($id) {
        $this->layout = false;
        $this->autoRender = false;
        $translation = $this->Translation->find('first', array('conditions' => array('Translation.id' => $id)));

        $result = array('ok' => false, 'msg' => '', 'id' => $id);

        try {
            if (empty($translation))
                throw new Exception('There is no such translations');

            if (!$this->Translation->updateAll(array(
                'Translation.status' => Translation::IGNORED
            ), array('Translation.id' => $id))) {
                throw new Exception('Unable to save state');
            }
            $result['ok'] = true;
        } catch (Exception $e) {
            $result['msg'] = $e->getMessage();
        }
        Configure::write('debug', 0);
        echo json_encode($result);
    }

}
