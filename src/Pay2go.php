<?php

namespace Pay2go;

use Exception;

class CreditCard
{
    protected $order = [];
    protected $merchant_id = null;
    protected $iv = null;
    protected $key = null;
    protected $version = 1.0;
    protected $token = null;
    protected $token_term = null;
    protected $isProd = false;

    public function __construct()
    {
        $this->merchant_id = config('pay2go.merchant_id');
        $this->key = config('pay2go.merchant_key');
        $this->iv = config('pay2go.merchant_iv');
        $this->isProd = config('pay2go.is_prod');

        if (is_null($this->merchant_id) || is_null($this->iv) || is_null($this->key)) {
            throw new Exception('there are some config lost. please check your config');
        }
    }

    public function getCreditCardRefundAPIUrl()
    {
        if ($this->isProd) {
            return 'https://core.spgateway.com/API/CreditCard/Close';
        } else {
            return 'https://ccore.spgateway.com/API/CreditCard/Close';
        }
    }

    public function getCreditCardAPIUrl()
    {
        if ($this->isProd) {
            return 'https://core.spgateway.com/API/CreditCard';
        } else {
            return 'https://ccore.spgateway.com/API/CreditCard';
        }
    }

    public function createMpgAesEncrypt($parameter = "", $key = "", $iv = "")
    {
        $returnStr = '';
        if (!empty($parameter)) {
            //將參數經過 URL ENCODED QUERY STRING
            $returnStr = http_build_query($parameter);
        }

        return trim(bin2hex(openssl_encrypt($this->addPadding($returnStr), 'aes-256-cbc', $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv)));
    }

    public function addPadding($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);

        return $string;
    }

    public function createOrder(array $order): CreditCard
    {
        $this->order = $order;
        $this->order['MerchantID'] = $this->merchant_id;

        return $this;
    }

    public function setVersion(float $version): CreditCard
    {
        $this->version = $version;

        return $this;
    }

    public function encrypt()
    {
        //交易資料經 AES 加密後取得 TradeInfo
        $tradeInfo = $this->createMpgAesEncrypt($this->order, $this->key, $this->iv);
        $shaInfo = strtoupper(hash('sha256', 'HashKey=' . $this->key . '&' . $tradeInfo . '&HashIV=' . $this->iv));

        return [
            'TradeInfo' => $tradeInfo,
            'TradeSha' => $shaInfo,
        ];
    }

    public function setToken(string $token, string $tokenTerm): CreditCard
    {
        $this->token = $token;
        $this->token_term = $tokenTerm;

        return $this;
    }

    public function refund(string $tradeNo, int $amount)
    {
        $postData = [
            'RespondType' => 'JSON',
            'Amt' => $amount,
            'MerchantOrderNo' => '',
            'IndexType' => 2,
            'TimeStamp' => time(),
            'TradeNo' => $tradeNo,
            'CloseType' => 2,
            'Cancel' => 1
        ];

        $this->order = $postData;
        $encrypt = $this->encrypt();

        $request = [
            'MerchantID_' => $this->merchant_id,
            'PostData_' => $encrypt['TradeInfo'],
        ];

        return $this->post($request);
    }

    public function payForToken()
    {
        $postData = [
            'TokenSwitch' => 'on',
            'TokenTerm' => $this->token_term,
            'TokenValue' => $this->token,
            'Version' => $this->version,
            'TimeStamp' => time()
        ];

        $this->order = array_merge($postData, $this->order);
        $encrypt = $this->encrypt();

        $request = [
            'MerchantID_' => $this->merchant_id,
            'PostData_' => $encrypt['TradeInfo'],
            'Pos_' => 'JSON'
        ];

        return $this->post($request);
    }

    public function post($postData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getCreditCardAPIUrl());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
