<?php


namespace Llabbasmkhll\LaravelTron;

use IEXBase\TronAPI\TransactionBuilder;
use IEXBase\TronAPI\Tron as TronBase;
use IEXBase\TronAPI\TronAddress;
use IEXBase\TronAPI\TronManager;
use IEXBase\TronAPI\TronScan;
use Llabbasmkhll\LaravelTron\Providers\HttpProvider;

class Tron extends TronBase
{

    private HttpProvider $solidityNode;
    private HttpProvider $eventServer;
    private HttpProvider $signServer;
    private HttpProvider $fullNode;
    private HttpProvider $explorer;

    public function __construct(
        $fullNode = null,
        $solidityNode = null,
        $eventServer = null,
        $signServer = null,
        $privateKey = null
    ) {
        $this->full($fullNode ?? config('tron.host.full'));
        $this->solidity($solidityNode ?? config('tron.host.solidity'));
        $this->event($eventServer ?? config('tron.host.event'));
        $this->sign($signServer ?? config('tron.host.sign'));
        $this->explorer($signServer ?? config('tron.host.scan'));
        $this->setPrivateKey($privateKey ?? config('tron.wallet.private_key'));

        $this->setManager(
            new TronManager($this, [
                'fullNode'     => $this->fullNode,
                'solidityNode' => $this->solidityNode,
                'eventServer'  => $this->eventServer,
                'signServer'   => $this->signServer,
            ])
        );

        $this->setScan(new TronScan($this->explorer));

        $this->transactionBuilder = new TransactionBuilder($this);
    }

    /**
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function full(string $fullNode): Tron
    {
        $this->fullNode = $this->httpClient($fullNode);

        return $this;
    }

    /**
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function httpClient(string $host): HttpProvider
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];
        if (config('tron.key') !== null) {
            $headers['TRON-PRO-API-KEY'] = config('tron.key');
        }

        return new HttpProvider($host, 30000, false, false, $headers);
    }

    /**
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function solidity(string $solidityNode): static
    {
        $this->solidityNode = $this->httpClient($solidityNode);

        return $this;
    }

    /**
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function explorer(string $explorer): static
    {
        $this->explorer = $this->httpClient($explorer);

        return $this;
    }

    /**
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function event(string $eventServer): static
    {
        $this->eventServer = $this->httpClient($eventServer);

        return $this;
    }

    /**
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function sign(string $signServer): static
    {
        $this->signServer = $this->httpClient($signServer);

        return $this;
    }

    public function key(string $privateKey): static
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function generate(): TronAddress
    {
        return $this->generateAddress();
    }

}
