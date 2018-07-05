<?php
namespace  framework\util;

class Des
{
    public function ecbEncrypt($key = "", $encrypt) {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $decrypted = mcrypt_encrypt(MCRYPT_DES, $key, $encrypt, MCRYPT_MODE_ECB, $iv);
        $encode = base64_encode($decrypted);
        return $encode;
    }

    public function ecbDecrypt($key = "", $decrypt) {
        $decoded = base64_decode($decrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $decrypted = mcrypt_decrypt(MCRYPT_DES, $key, $decoded, MCRYPT_MODE_ECB, $iv);
        return self::trimEnd($decrypted);
    }
    /**
     * des-ecb加密
     * @param string  $data 要被加密的数据
     * @param string  $key 加密密钥
     */
    function des_ecb_encrypt($data, $key){
        return openssl_encrypt ($data, 'des-ecb', $key);
    }

    /**
     * des-ecb解密
     * @param string  $data 加密数据
     * @param string  $key 加密密钥
     */
    function des_ecb_decrypt ($data, $key){
        return openssl_decrypt ($data, 'des-ecb', $key);
    }
    /**
     * des-cbc加密
     * @param string  $data 要被加密的数据
     * @param string  $key 加密使用的key
     * @param string  $iv 初始向量
     */
    function des_cbc_encrypt($data, $key, $iv){
        return openssl_encrypt ($data, 'des-cbc', $key, 0, $iv);
    }

    /**
     * des-cbc解密
     * @param string  $data 加密数据
     * @param string  $key 加密使用的key
     * @param string  $iv 初始向量
     */
    function des_cbc_decrypt($data, $key, $iv){
        return openssl_decrypt ($data, 'des-cbc', $key, 0, $iv);
    }
    /*
     * 去掉填充的字符
     */

    private function trimEnd($text) {
        $len = strlen($text);
        $c = $text[$len - 1];

        if (ord($c) == 0) {
            return rtrim($text, $c);
        }

        if (ord($c) < $len) {
            for ($i = $len - ord($c); $i < $len; $i++) {
                if ($text[$i] != $c) {
                    return $text;
                }
            }
            return substr($text, 0, $len - ord($c));
        }
        return $text;
    }
}

