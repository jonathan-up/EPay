<?php
/**
 * Class EPay
 *
 * DEV IN PHP7.1.9
 * @author Jonathan <77266837@qq.com>
 */

namespace JonathanUp;

class EPay
{
    /*
     * 商户信息
     * */
    private $pid;
    private $key;
    private $url;

    /*
     * 订单参数
     * */
    private $type;
    private $notifyUrl;
    private $returnUrl;
    private $outTradeNo;
    private $name;
    private $money;

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param mixed $notifyUrl
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    /**
     * @param mixed $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @param mixed $outTradeNo
     */
    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $money
     */
    public function setMoney($money)
    {
        $this->money = $money;
    }

    /**
     * EPay constructor.
     *
     * @param $pid
     * @param $key
     * @param $url
     */
    public function __construct($pid, $key, $url)
    {
        $this->pid = $pid;
        $this->key = $key;
        $this->url = $url;
    }

    /**
     * 创建订单
     *
     * @return string
     */
    public function order()
    {
        $api = $this->url.'submit.php';
        $data = [
            "pid"          => trim($this->pid),
            "type"         => $this->type,
            "notify_url"   => $this->notifyUrl,
            "return_url"   => $this->returnUrl,
            "out_trade_no" => $this->outTradeNo,
            "name"         => $this->name,
            "money"        => (float)$this->money
        ];

        $para_filter  = self::paraFilter($data);
        $para_sort    = self::argSort($para_filter);

        $signString   = self::createLinkString($para_sort);
        $data['sign'] = $this->getSign($signString);

        $sHtml = "<form id='epay' name='epay' action='{$api}' method='post'>";
        foreach ($data as $key => $val) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='正在跳转支付...'></form>";

        $sHtml = $sHtml."<script>document.forms['epay'].submit();</script>";

        return $sHtml;
    }

    /**
     * 验证签名
     *
     * @param $data
     * @return bool
     */
    public function verifySign($data)
    {
        $para_filter  = self::paraFilter($data);
        $para_sort    = self::argSort($para_filter);

        $signString   = self::createLinkString($para_sort);
        $mySign = $this->getSign($signString);

        if ($mySign === $data['sign']) {
            return true;
        }
        return false;
    }

    /**
     * 数组转query字符串
     *
     * @param $para
     * @return false|string
     */
    protected static function createLinkString($para)
    {
        $arg = "";
        foreach ($para as $key => $val) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, -1);

        return $arg;
    }

    /**
     * 参数过滤
     *
     * @param $para
     * @return array
     */
    protected static function paraFilter($para)
    {
        $para_filter = [];
        foreach ($para as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val == "") {
                continue;
            } else {
                $para_filter[$key] = $para[$key];
            }
        }
        return $para_filter;
    }

    /**
     * 参数排序
     *
     * @param $para
     * @return mixed
     */
    protected static function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 获取签名
     *
     * @param $string
     * @return string
     */
    protected function getSign($string)
    {
        return md5($string.$this->key);
    }
}
