<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController
{

    public function __construct()
    {
    }
/*
    items pending 
    proper validation ,
    proper function names ,
    FE part , with curl rqst,
    thorough testing,
    proper proj names,
    same helper function names.
*/

    public function mainFunction(Request $rqst)
    {
        $data = $rqst->data;


        switch ($this->checkTypeOfStream($data)) {

            case 1:
                $parsedData =  $this->jsonToArray($data);
                $isAssocFlag =  $this->checkAssocArray($parsedData);
                if ($isAssocFlag) {
                } else {
                    return false;
                }

                if ($this->checkIsNestedMultidemsionalArray($parsedData)) {
                    return false;
                } else {
                    $returnData = $this->getKeyValuePairData($parsedData);

                    if ($returnData === false) {
                        return false; //show the message
                    } else {
                        return $returnData;
                    }
                }
                break;

            case 2:
                $isAssocFlag =  $this->checkAssocArray($data);
                if ($isAssocFlag) {
                } else {
                    return false;
                }

                if ($this->checkIsNestedMultidemsionalArray($data)) {
                    return false;
                } else {
                    $returnData = $this->getKeyValuePairData($data);

                    if ($returnData === false) {
                        return false; //show the message
                    } else {
                        return $returnData;
                    }
                }
                break;

            default:
                //write the code for the collection , future scope.
                break;
        }
    }


    public function checkTypeOfStream($data)
    {
        //0 -> Invalid Data , 1 => Json , 2 => Array
        if ($this->checkIsJSON($data)) {
            return  1;
        } elseif ($this->checkIsArray($data)) {
            return  2;
        }
    }


    public function checkIsJSON(string $string): string
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public function checkIsArray($data)
    {
        return is_array($data) ? true : false;
    }

    public function jsonToArray(string $data): array
    {

        return json_decode(json_encode($data), true);
    }


    public function checkAssocArray(array $data): string
    {

        $isAssocFlag = false;

        foreach ($data as $k => $v) {

            if (is_string($k)) {
                $isAssocFlag = true;
            } else {
                $isAssocFlag = false;
            }
        }

        return $isAssocFlag;
    }

    public function checkKeysSameAcross(array $data): array
    {
        $noOfKeyCount = [];

        for ($i = 0; $i < count($data); $i++) {

            array_push($noOfKeyCount, count($data[$i]));
        }

        $unEventCount = false;
        for ($i = 0; $i < (count($noOfKeyCount) - 1); $i++) {

            if ($noOfKeyCount[$i] !== $noOfKeyCount[$i + 1]) {
                $unEventCount = true;
            }
        }

        if ($unEventCount) {
            return false;
        }

        $unMatchingKeyNames = false;

        for ($i = 0; ($i < count($data) - 1); $i++) {

            if ($data[$i] !== $data[$i + 1]) {
                $unMatchingKeyNames = true;
            }
        }

        if ($unMatchingKeyNames) {
            return false;
        }

        return $data;
    }



    public function checkIsNestedMultidemsionalArray(array $data): string
    {

        $isNestedMultiDimensional = false;
        foreach ($data as $k => $v) {

            if (is_array($data[$k])) {
                $isNestedMultiDimensional = true;
                break;
            }
        }

        return $isNestedMultiDimensional;
    }

    public function getKeyValuePairData(array $data): string
    {

        $keys = [];
        $values = [];

        if (count($data) == 1) {

            $keys[] =  array_keys($data);
            $values[] = array_values($data);
        } else {

            for ($i = 0; $i < count($data); $i++) {

                $keys[$i] = array_keys($data[$i]);
            }

            $returnData = $this->checkKeysSameAcross($keys);

            if ($returnData === false) {
                return false;
            }

            $values = [];

            for ($i = 0; $i < count($data); $i++) {

                $values[$i] = array_values($data[$i]);
            }
        }
        $sqlQuery = $this->getSQLInsertStatement($keys[0], $values);
        return $sqlQuery;
    }

    public function getSQLInsertStatement(array $keys, array $value): string
    {

        $query = "Insert into table_name(" .  implode(',', $keys) . ')values(' . implode(',', $value) . ')';
        if (count($value) > 1) {
            $initialQuery = "Insert into table_name(" .  implode(',', $keys) . ')values(' . implode(',', $value) . ')';
            $otherPart = '';
            for ($i = 1; $i < count($value); $i++) {
                $otherPart = $otherPart . " ,(" . $value[$i] . ")";
            }
            $query = $initialQuery . $otherPart;
        }

        return $query;
    }
}
