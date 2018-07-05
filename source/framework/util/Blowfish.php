<?php
namespace framework\util;
/**
 * php blowfish 算法
 * Class blowfish
 */
class Blowfish{
    public $key;
    public $iv; //偏移量

    function __construct($key, $iv = 0) {
        //key长度8例如:1234abcd
        $this->key = $key;
        if ($iv == 0) {
            $this->iv = $key; //默认以$key 作为 iv
        } else {
            $this->iv = $iv; //mcrypt_create_iv ( mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_CBC), MCRYPT_DEV_RANDOM );
        }
    }
    /**
     * blowfish + cbc模式 + pkcs5补码 加密
     * @param string $str 需要加密的数据
     * @return string 加密后base64加密的数据
     */
    public function blowfish_cbc_pkcs5_encrypt($str)
    {
        $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');

        //pkcs5补码
        $size = mcrypt_get_block_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        $str = $this->pkcs5_pad($str, $size);

        if (mcrypt_generic_init($cipher, $this->key, $this->iv) != -1)
        {
            $cipherText = mcrypt_generic($cipher, $str);
            mcrypt_generic_deinit($cipher);

            return base64_encode($cipherText);
        }

        mcrypt_module_close($cipher);
    }

    /**
     * blowfish + cbc模式 + pkcs5 解密 去补码
     * @param string $str 加密的数据
     * @return string 解密的数据
     */
    public function blowfish_cbc_pkcs5_decrypt($str)
    {
        $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');

        if (mcrypt_generic_init($cipher, $this->key, $this->iv) != -1)
        {
            $cipherText = mdecrypt_generic($cipher, base64_decode($str));
            mcrypt_generic_deinit($cipher);

            return $this->pkcs5_unpad($cipherText);
        }

        mcrypt_module_close($cipher);
    }

    private function pkcs5_pad($text, $blocksize){
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }

    private function pkcs5_unpad($str){
        $pad = ord($str[($len = strlen($str)) - 1]);
        return substr($str, 0, strlen($str) - $pad);
    }
}