<?php
class paypalproView extends view {
    public function getForm() {
        $inputFields = frame::_()->getModule('paypalpro')->getInputFields();
        $ccTypes = frame::_()->getModule('paypalpro')->getCcTypes();
        $types_array = array();
        foreach($ccTypes as $key => $value) {
            if (frame::_()->getModule('paypalpro')->isCardAccepted($key)) {
                $types_array[$key] = $value;
            }
        }

        $today = getdate();

        $months_array = array();
        for ($i=1; $i<13; $i++) {
            $months_array[sprintf('%02d', $i)] = strftime('%B', mktime(0,0,0,$i,1,2000));
        }

        $year_valid_from_array = array();
        for ($i=$today['year']-10; $i < $today['year']+1; $i++) {
            $year_valid_from_array[strftime('%Y',mktime(0,0,0,1,1,$i))] = strftime('%Y',mktime(0,0,0,1,1,$i));
        }

        $year_expires_array = array();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
            $year_expires_array[strftime('%Y',mktime(0,0,0,1,1,$i))] = strftime('%Y',mktime(0,0,0,1,1,$i));
        }
        
        $fields = array();
        foreach($inputFields as $key => $inp) {
            $f = new field($key, $inp['type']);
            switch($key) {
                case 'cc_type':
                    $f->addHtmlParam('options', $types_array);
                    break;
                case 'cc_starts_month':
                case 'cc_expires_month';
                    $f->addHtmlParam('options', $months_array);
                    break;
                case 'cc_starts_year':
                    $f->addHtmlParam('options', $year_valid_from_array);
                    break;
                case 'cc_expires_year':
                    $f->addHtmlParam('options', $year_expires_array);
                    break;
            }
            $fields[$key] = $f;
        }
        $this->assign('fields', $fields);
        return parent::getContent('form');
    }
}
?>
