<?php

namespace PlacetoPay\MPI\Tests\Functionality;

use PlacetoPay\MPI\MPIService;
use PlacetoPay\MPI\Tests\BaseTestCase;

class QueryProcessTest extends BaseTestCase
{
    public function create($overwrite = [])
    {
        return new MPIService(array_merge([
            'url' => getenv('MPI_URL'),
            'apiKey' => getenv('MPI_API_KEY'),
            'client' => new \PlacetoPay\MPI\Clients\MockClient(),
        ], $overwrite));
    }

    public function testItObtainsAQuerySuccessfully()
    {
        $mpi = $this->create();

        $response = $mpi->query(1);

        $this->assertTrue($response->isAuthenticated());
        $this->assertTrue($response->validSignature());
        $this->assertEquals('05', $response->eci());
        $this->assertEquals('AAACCZJiUGVlF4U5AmJQEwAAAAA=', $response->cavv());
        $this->assertEquals('Z8UuHYF8Epz46M8V/MkGJDl2Y5E=', $response->xid());
    }

    public function testItDoesNotAuthenticateWhenResponseIsInvalid()
    {
        $mpi = $this->create();

        $response = $mpi->query(2);

        $this->assertFalse($response->isAuthenticated());
        $this->assertTrue($response->validSignature());
        $this->assertEquals('06', $response->eci());
        $this->assertEquals('CAACAlRGNFVVBEYZGUY0EwAAAAA=', $response->cavv());
        $this->assertEquals('0CI2blBv4uSnIqelFXJX0mV+fMg=', $response->xid());
    }

    public function testItDoesNotFallForInvalidAuthentications()
    {
        $mpi = $this->create();

        $response = $mpi->query(3);

        $this->assertFalse($response->isAuthenticated());
        $this->assertFalse($response->validSignature());
        $this->assertEquals('05', $response->eci());
        $this->assertEquals('AAACA1aTWUhYcxeGg5NZEAAAEAA=', $response->cavv());
        $this->assertEquals('UbRrlDARTXFT8GVALigF4MDyhkk=', $response->xid());
    }

    public function testItGetsAnArrayWithTheInformation()
    {
        $mpi = $this->create();

        $response = $mpi->query(1);

        $this->assertEquals([
            'validSignature' => true,
            'eci' => '05',
            'cavv' => 'AAACCZJiUGVlF4U5AmJQEwAAAAA=',
            'xid' => 'Z8UuHYF8Epz46M8V/MkGJDl2Y5E=',
            'enrolled' => 'Y',
            'authenticated' => 'Y',
        ], $response->toArray());
    }
}