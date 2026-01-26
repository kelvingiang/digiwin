<?php

include_once DIR_CLASS . 'sendmail.php';

class Order_Class {

    private $tblOrder;
    private $tblOrderDetail;

    public function __construct() {

        global $wpdb;
        $this->tblOrder = $wpdb->prefix . 'order';
        $this->tblOrderDetail = $wpdb->prefix . 'order_detail';
    }

    public function saveOrder($info = array()) {
        global $wpdb;

        echo '<pre>';
        print_r($info);
        echo '</pre>';



        $infoData = array(
            'code' => $info['hid-serial'],
            'company' => $info['txt-company'],
            'name' => $info['txt-name'],
            'address' => $info['txt-address'],
            'phone' => $info['txt-phone'],
            'email' => $info['txt-email'],
            'payment' => $info['pay-ment'],
            'total' => $info['hid-total'],
            'status' => 1,
            'trash' => 0,
            'create_date' => date('d-m-Y'),
            'note' => $info['txt-note'],
        );

        $memberInfo = $wpdb->insert($this->tblOrder, $infoData);

        if ($memberInfo) {
            $pid = $wpdb->insert_id;
// $dataCount = array_count_values($data);
            $detailArr = array();
            foreach ($info['hid_item'] as $key => $val) {
                $sp = explode('|', $val);
                echo '<pre>';
                print_r($sp);
                echo '</pre>';

                $detailData = array(
                    'order_ID' => $pid,
                    'product_ID' => $pid,
                    'product_serial' => $sp[0],
                    'product_color' => $sp[6],
                    'product_size' => $sp[2] . '&quot;',
                    'product_specifi' => $sp[3],
                    'product_weight' => $sp[4],
                    'product_price' => $sp[5],
                    'product_qty' => $info['txt-qty'][$key],
                );
                $wpdb->insert($this->tblOrderDetail, $detailData);

                $detailDataForMail = array(
                    'order_ID' => $pid,
                    'product_ID' => $pid,
                    'product_serial' => $sp[0],
                    'product_color' => $sp[1],
                    'product_size' => $sp[2] . '&quot;',
                    'product_specifi' => $sp[3],
                    'product_weight' => $sp[4],
                    'product_price' => $sp[5],
                    'product_qty' => $info['txt-qty'][$key],
                );
                $detailArr[] = $detailDataForMail;
            }

            $sendMail = new SendMailClass();
            $mailList = array(get_option('company_email'), $info['txt-email']);


            $sendMail->sendMail($mailList, $infoData, $detailArr);

            return TRUE;
        } else {
            return FALSE;
        }
    }

}
