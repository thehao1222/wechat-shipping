<?php

namespace Li_thehao\WechatShipping;

class WechatShipping
{
    /**
     * @description 同步微信订单物流状态
     * @param string $accesstoken 微信访问令牌
     * @param string $ordersn 订单号
     * @param string $lc_id 物流公司ID
     * @param string $l_id 物流单号
     * @param int $shippingtime 发货时间戳
     * @param string $user_openid 用户OpenID
     * @param string $phone 联系电话
     * @param string $mchid 商户号
     * @return array 微信API响应数据
     */
    public function shippingOrder($accesstoken, $ordersn, $lc_id, $l_id, $shippingtime, $user_openid, $phone ,$mchid)
    {
        $apiUrl = "https://api.weixin.qq.com/wxa/sec/order/upload_shipping_info?access_token=$accesstoken";
        $milliseconds = round(microtime(true) * 1000) % 1000;
        $uploadTime = date('Y-m-d\TH:i:s', $shippingtime) . '.' . sprintf('%03d', $milliseconds) . date('P');

        // 示例请求数据
        $requestData = [
            "order_key" => [
                "order_number_type" => 1,
                "mchid" => (string)$mchid,
                "out_trade_no" => (string)$ordersn
            ],
            "logistics_type" => 1,
            "delivery_mode" => 1,
            "shipping_list" => [
                [
                    "tracking_no" => (string)$l_id,
                    "express_company" => (string)$lc_id,
                    "item_desc" => '微信订单',
                    "contact" => [
                        "consignor_contact" => (string)$phone,
                    ]
                ]
            ],
            "upload_time" => (string)$uploadTime,
            "payer" => [
                "openid" => $user_openid,
            ],
        ];

        // 将请求数据转换为 JSON 格式
        $jsonData = json_encode($requestData, JSON_UNESCAPED_UNICODE);

        // 初始化 cURL
        $ch = curl_init($apiUrl);

        // 设置 cURL 选项
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        // 执行 cURL 请求
        $response = curl_exec($ch);

        // 检查是否有错误
        if (curl_errno($ch)) {
            throw new \RuntimeException('Curl error: ' . curl_error($ch));
        }

        // 关闭 cURL
        curl_close($ch);

        // 解析响应数据
        $responseData = json_decode($response, true);

        return $responseData;
    }

    /**
     * @description 电话号码加密
     * @param string $phone 电话号码
     * @return string 加密后的电话号码
     */
    public function maskPhoneNumber($phone)
    {
        // 先检查传入的字符串是否是合法的 11 位电话号码
        if (strlen($phone) === 11 && ctype_digit($phone)) {
            // 截取电话号码的前三位
            $firstPart = substr($phone, 0, 3);
            // 截取电话号码的后四位
            $lastPart = substr($phone, -4);
            // 中间四位用星号代替
            $mask = '****';
            // 拼接处理后的电话号码
            return $firstPart . $mask . $lastPart;
        }
        // 如果不是合法的 11 位电话号码则原样返回
        return $phone;
    }
}