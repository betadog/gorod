<?php

class TroykaService
{
    const DATE_FORMAT = 'YmdHis';
    const PASSPHRASE  = 'testPC01';
    const PARTNER_ID  = 1223126;
    const TERMINAL    = 'test_terminal';
    const LOCATION    = 'test_location';

    /** @required */
    public $mode = 'test',
        $passphrase = self::PASSPHRASE,
        $partnerId  = self::PARTNER_ID,
        $terminal   = self::TERMINAL,
        $location   = self::LOCATION;

    /** @var SoapClient */
    private $client;

    public function init()
    {
        $this->client = new SoapClient('CFTLoyaltyPCPoints.wsdl', [
            'location' => "https://xml-online.demo.korona.net:20101/axis.v3/services/CFTLoyaltyPCPoints_SoapPort_term_2.7.6",
            'trace' => 1,
            'exceptions' => true,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'cafile'     => "{$this->mode}-root.pem",
                    'local_cert' => "{$this->mode}-client_ssl.pem",
                    'passphrase' => $this->passphrase,
                ],
            ]),
        ]);
    }


    public function getInfo($pan)
    {
        $data = new InfoRequestData();
        $data->transaction = new TransactionData();
        $data->transaction->id        = 11;
        $data->transaction->dateTime  = date(TroykaService::DATE_FORMAT);
        $data->transaction->location  = $this->location;
        $data->transaction->terminal  = $this->terminal;
        $data->transaction->partnerId = $this->partnerId;
        $data->transaction->pan       = $pan;

//        $this->client->__setSoapHeaders([
//            new SoapHeader('NAMESPACE', 'SOAPAction', ),
//        ]);
//$headers[] = new SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $authvalues,true);

        $result = $this->client->getInfo2( $data );

        $response = [
            'headers' => $this->client->__getLastRequestHeaders(),
            'request' => $this->client->__getLastRequest(),
        ];

        var_dump($response);
        return $result;
    }

}


class TransactionData
{
    /** @var integer, required */
    public $id;         // Идентификатор транзакции. В зависимости от типа подключения присваивается либо POS’ом, либо АC.
    // Всегда входит в состав уникального идентификатора операции. N.B. Значение данного поля является
    // регистро-независимым (ПЦ хранит значение в верхнем регистре)
    /** @var string, required */
    public $terminal;   // Идентификатор кассы (POS) – источника операции. В случае прямого
    // подключения входит в состав уникального идентификатора операции
    /** @var string, required */
    public $location;   // Код места установки POS’а (например, магазина). В случае прямого
    // подключения входит в состав уникального идентификатора операции
    /** @var integer, required */
    public $partnerId;  // Идентификатор Участника, под которым он зарегистрирован в ПЦ
    /** @var string */
    public $pan;        // Номер карты, напечатанный на карте. Обязательно присутствует при ручном вводе номера карты
    /** @var string, required */
    public $dateTime;   // Локальные для POS дата и время совершения операции в формате YYYYMMDDhhmmss
}
class InfoRequestData
{
    /** @var TransactionData, required */
    public $transaction;
    /** @var  ChequeItem[], optional */
    public $cheque;
    /** @var boolean, optional */
    public $getCardholder = 1;  // «Флаг» формирования информации о держателе карты в ответе на операцию.
    // Принимает значения 0 (не формировать; по умолчанию) или 1 (формировать).
    /** @var boolean, optional */
    public $getAccStatement = 0;// «Флаг» формирования выписки по счету карты или держателя карты в ответе на операцию.
    // Принимает значения 0 (не формировать; по умолчанию) или 1 (формировать).
    /** @var  AccStatementParams, optional */
    public $accStatementParams; // Параметры формирования выписки. Передается только вместе с атрибутом getAccStatement
    /** @var boolean, optional */
    public $getPreCalcBns;      // «Флаг» предварительного расчета бонусов по чеку, без движений по счетам.
    // Принимает значения 0 (не производить расчет; по умолчанию) или 1 (производить)
    /** @var  string, optional */
    //public $currency;           // Если данный атрибут не передается, то используется умолчательная валюта сети.
    /** @var  string, optional */
    public $getBnsActiveRestrictInfo;   // «Флаг» формирования выписки по маркированным баллас с ограничением по их списанию.
    // Принимает значения 0 (не формировать; по умолчанию) или 1 (формировать).
}
class ChequeItem
{
    /** @var  string */
    public $product;    // Код товара по справочнику товаров
    /** @var  float */
    public $Quantity;   // Количество товара в единицах измерения товара
    /** @var  integer */
    public $amount;     // Стоимость за указанное количество товара в минимальных единицах в валюте чека (например, копейках).
    /** @var  integer */
    public $position;   // Номер позиции в чеке. Может использоваться для настройки правил акции
    /** @var  ChequeItemAttr[] */
    public $attributes; // Массив дополнительных свойств товара по позиции. Может использоваться для настройки правил акции.
}
class ChequeItemAttr
{
    /** @var  string(256) */
    public $name;
    /** @var  string(2000) */
    public $value;
}
class AccStatementParams
{
    /** @var  integer */
    public $countLimit; // Целое число от 1 до 200 включительно. Лимит на количество операций в выписке.
    // Переопределяет настройку на ПЦ
}