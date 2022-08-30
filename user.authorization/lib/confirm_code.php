<?php
use Custom\Security\Encryption\Workers;

class ConfirmCode
{
    private $encryptingOn = true;
    private $obEncrypting;

    /**
     * Method set value property encryptingCode
     *
     * @param bool $value
     */
    public function setEncryptingOn(bool $value)
    {
        $this->encryptingOn = $value;
    }

    /**
     * Method set object encryption
     *
     * @param object $object
     *
     * @throws Exception
     */
    public function setEncryptionObject($object)
    {
        if (!($object instanceof Workers\DecryptingInterface) || !($object instanceof Workers\EncryptingInterface)) {
            throw new \Exception('In object is not correct interfaces! ' . __CLASS__ . ' ' . __METHOD__ . " line " . __LINE__);
        }
        $this->obEncrypting = $object;
    }

    /**
     * Method generate phone code for send user and validation crypt for code
     *
     * @return array|bool
     */
    public function generateConfirmCode()
    {
        $code = mt_rand(100000, 999900);
        if ($code > 0) {
            return $code;
        }
        return false;
    }

    /**
     * Method encrypting confirm code for validation
     *
     * @param int $code
     *
     * @return bool
     */
    public function generateConfirmCodeCrypt(int $code)
    {
        if (!empty($this->obEncrypting && is_object($this->obEncrypting))) {
            return $this->obEncrypting->encryptingData($code);
        }
        return false;
    }

    /**
     * Check entry phone code
     *
     * @param $crypt
     * @param $codeEnter
     *
     * @return bool
     */
    public function validateEnterCode(string $crypt, int $codeEnter)
    {
        if (!empty($this->obEncrypting && is_object($this->obEncrypting))) {

            $cryptDecode = html_entity_decode($crypt);
            $originalCode = $this->obEncrypting->decryptionData($cryptDecode);
            if (intval($originalCode) === $codeEnter) {
                return true;
            }
        }

        return false;
    }
}