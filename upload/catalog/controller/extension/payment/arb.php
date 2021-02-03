<?php

class ControllerExtensionPaymentArb extends Controller
{
    const ARB_HOSTED_ENDPOINT = 'https://securepayments.alrajhibank.com.sa/pg/payment/hosted.htm';
    const ARB_PAYMENT_ENDPOINT = 'https://securepayments.alrajhibank.com.sa/pg/paymentpage.htm?PaymentID=';
    const ARB_SUCCESS_STATUS = 'CAPTURED';

    private $orderInfo;

    public function index()
    {

        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');

        $this->order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['ap_trans_id'] = $this->config->get('payment_arb_trans_id');
        $data['ap_amount'] = $this->currency->format($this->order_info['total'], $this->order_info['currency_code'], $this->order_info['currency_value'], false);
        $data['ap_currency'] = $this->order_info['currency_code'];
        $data['ap_purchasetype'] = 'Item';
        $data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
        $data['ap_itemcode'] = $this->session->data['order_id'];
        $data['ap_returnurl'] = $this->url->link('extension/payment/arb/result&flag=success');
        $data['ap_cancelurl'] = $this->url->link('extension/payment/arb/result&flag=failure', '', true);
        $paymentId = $this->getPaymentId();
        $data['action'] = self::ARB_PAYMENT_ENDPOINT . $paymentId;
        return $this->load->view('extension/payment/arb', $data);
    }

    public function result()
    {
//        header('Set-Cookie: ' . $this->config->get('session_name') . '=' . $this->session->getId() . '; HttpOnly; Secure');
        $this->load->model('checkout/order');

        $decrypted = $this->aesDecrypt($_REQUEST["trandata"]);
        $raw = urldecode($decrypted);
        $dataArr = json_decode($raw, true);

        $sessionId = $dataArr[0]["udf2"];
        $paymentStatus = $dataArr[0]["result"];
        $orderId = $dataArr[0]["udf1"];

        header('Set-Cookie: ' . $this->config->get('session_name') . '=' . $sessionId . '; HttpOnly; Secure');

        if (isset($paymentStatus) && $paymentStatus === self::ARB_SUCCESS_STATUS) {
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($orderId, $this->config->get('payment_arb_order_status_id'));
            $this->response->redirect($this->url->link('checkout/success'));
        }

        $this->response->redirect($this->url->link('checkout/failure'));

    }

    private function getPaymentId()
    {

        $plainData = $this->getRequestData();
        $wrappedData = $this->wrapData($plainData);

        $encData = [
            "id" => $this->config->get('payment_arb_trans_id'),
            "trandata" => $this->aesEncrypt($wrappedData),
            "errorURL" => $this->url->link('checkout/success'),
            "responseURL" => $this->url->link('checkout/failure')
        ];
        $wrappedData = $this->wrapData(json_encode($encData, JSON_UNESCAPED_SLASHES));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::ARB_HOSTED_ENDPOINT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $wrappedData,

            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Accept-Language: application/json',
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // parse response and get id
        $data = json_decode($response, true)[0];
        if ($data["status"] == "1") {
            $id = explode(":", $data["result"])[0];
            return $id;
        } else {
            // handle error either refresh on contact merchant
            return -1;
        }
    }

    private function getRequestData()
    {

        $this->load->model('checkout/order');

        $amount = $this->currency->format($this->order_info['total'], $this->order_info['currency_code'], $this->order_info['currency_value'], false);

        $trackId = (string)rand(1, 1000000); // TODO: Change to real value

        $data = [
            "id" => $this->config->get('payment_arb_trans_id'),
            "password" => $this->config->get('payment_arb_security'),
            "action" => "1",
            "currencyCode" => "682",
            "errorURL" => $this->url->link('extension/payment/arb/result&flag=failure'),
            "responseURL" => $this->url->link('extension/payment/arb/result&flag=success'),
            "trackId" => $trackId,
            "amt" => $amount,
            "udf1" => $this->session->data["order_id"],
            "udf2" => $this->session->getId()
        ];

        $data = json_encode($data, JSON_UNESCAPED_SLASHES);

        return $data;
    }

    private function wrapData($data)
    {
        $data = <<<EOT
[$data]
EOT;
        return $data;
    }

    private function aesEncrypt($plainData)
    {
        $key = $this->config->get('payment_arb_resource');
        $iv = "PGKEYENCDECIVSPC";
        $str = $this->pkcs5_pad($plainData);
        $encrypted = openssl_encrypt($str, "aes-256-cbc", $key, OPENSSL_ZERO_PADDING, $iv);
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $encrypted = $this->byteArray2Hex($encrypted);
        $encrypted = urlencode($encrypted);
        return $encrypted;
    }

    private function pkcs5_pad($text, $blocksize = 16)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private function byteArray2Hex($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        return bin2hex($bin);
    }

    private function aesDecrypt($code)
    {
        $code = $this->hex2ByteArray(trim($code));
        $code = $this->byteArray2String($code);
        $iv = "PGKEYENCDECIVSPC";
        $key = $this->config->get('payment_arb_resource');
        $code = base64_encode($code);
        $decrypted = openssl_decrypt($code, 'AES-256-CBC', $key, OPENSSL_ZERO_PADDING,
            $iv);
        return $this->pkcs5_unpad($decrypted);
    }

    private function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }

    private function hex2ByteArray($hexString)
    {
        $string = hex2bin($hexString);
        return unpack('C*', $string);
    }

    private function byteArray2String($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        return join($chars);
    }

}

//https://demo.opencart.com/index.php?route=checkout/failure
//https://demo.opencart.com/index.php?route=checkout/success
//http://localhost/opencartx/index.php?route=checkout/success
//extension/payment/arb/callback&flag=success