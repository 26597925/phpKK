<?php
namespace framework\util;

class Http {

	static private function defaultHeader() {
		$header="User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12\r\n";
		$header.="Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$header.="Accept-language: zh-cn,zh;q=0.5\r\n";
		$header.="Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
		return $header;
	}

	static public function doGet($url, $timeout=5, $header="") {
		$header=empty($header)?self::defaultHeader():$header;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		if(preg_match('/^(https)/is', $url)){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ����֤���� �����κ�֤��
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 0 ����������񶼲���֤�ˣ�1���֤�����Ƿ�����������
		}

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));//模拟的header头
		$result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode == 200)
            return $result;

        return false;
	}

	static public function doPost($url, $post_data=array(), $timeout=5, $header=array()) {
		$header=empty($header)?'':$header;
		//$post_string = $post_data;
		$post_string = http_build_query($post_data);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_URL, $url);

		if(preg_match('/^(https)/is', $url)){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ����֤���� �����κ�֤��
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 0 ����������񶼲���֤�ˣ�1���֤�����Ƿ�����������
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//ģ���headerͷ
		$result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

        if($httpCode == 200)
            return $result;

        return false;
	}

    static public function doPost1($url, $post_data=array(), $timeout=5, $header=array()) {
        $header=empty($header)?'':$header;
        //$post_string = $post_data;
        $post_string = http_build_query($post_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_URL, $url);

        if(preg_match('/^(https)/is', $url)){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ����֤���� �����κ�֤��
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 0 ����������񶼲���֤�ˣ�1���֤�����Ƿ�����������
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//ģ���headerͷ
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode == 200)
            return $result;

        return false;
    }
}