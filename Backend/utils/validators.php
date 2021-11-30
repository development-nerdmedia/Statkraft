<?php

function validateEmail($email)
{
    $data = trim($email);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
    {
        return false;
    }
    return true;
}

function validate_DNI($DNI, $validatenine = false)
{
    $error=0;
    if($validatenine && strlen($DNI)==9)
    {
        $i=$suma=0;
        $multiplos = array(3,2,7,6,5,4,3,2);
        
        $array_number = array(6, 7, 8, 9, 0, 1, 1, 2, 3, 4, 5);
        $array_letters = array('K', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        
        $numdni = str_split(substr($DNI,0,-1)); // 8 digits
        $dcontrol = substr($DNI,-1); //1 digito
        
        foreach($numdni as $digito) {
            $suma+=$digito*$multiplos[$i];
            $i++;
        }
        
        $key = 11 - ($suma%11);
        $key = $key==11?0:$key;
        
        if(is_numeric($dcontrol)) {
            if($array_number[$key] != $dcontrol) {
                $error++;
                $this->msg='Su DNI no es v&aacute;lido. Por favor, ingrese uno correcto';
            }
        } else {
            $dcontrol = strtoupper($dcontrol);
            if($array_letters[$key] != $dcontrol) {
                $error++;
                $this->msg='Su DNI no es v&aacute;lido. Por favor, ingrese uno correcto';
            }
        }
    }elseif (!$validatenine && strlen($DNI) == 8 && is_numeric($DNI)) {
        
    }else{
        $error++;
    }
    return $error == 0?true:false;
}

function validate_Recaptcha($token, $curl)
{
    $curl->doPost();
    //$curl->doDebug();
    $curl->setUrl(Configure::read('recaptcha.host'));
    $curl->setPostFields(http_build_query([
        'secret' => Configure::read('recaptcha.secret'),
        'response' => $token,
    ]), false);
    
    $curl->call();
    $response = $curl->getResponse();
    if($response->success)
    {
        return true;
    }else{
        //e("ERROR: Curl reported => '" . $curl->getErrorMessage() . "' calling url '".$curl->getUrl()."' with parameters: ".print_r($curl->getAllOptions(), true)."\nGot response: ".$curl->getResponse(false));
        //prd($curl->getTransferInfo());
        return false;
    }
}