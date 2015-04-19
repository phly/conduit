<?php
namespace PhlyTest\Conduit\Http;

use Phly\Conduit\Http\Request;
use Phly\Http\ServerRequest as PsrRequest;
use Phly\Http\Uri;
use PHPUnit_Framework_TestCase as TestCase;

class RequestTest extends TestCase
{
    public function setUp()
    {
        $psrRequest     = new PsrRequest([], [], 'http://example.com/', 'GET', 'php://memory');
        $this->original = $psrRequest;
        $this->request  = new Request($this->original);
    }

    public function testCallingSetUriSetsUriInRequestAndOriginalRequestInClone()
    {
        $url = 'http://example.com/foo';
        $request = $this->request->withUri(new Uri($url));
        $this->assertNotSame($this->request, $request);
        $this->assertSame($this->original, $request->getOriginalRequest());
        $this->assertSame($url, (string) $request->getUri());
    }

    public function testConstructorSetsOriginalRequestIfNoneProvided()
    {
        $url = 'http://example.com/foo';
        $baseRequest = new PsrRequest([], [], $url, 'GET', 'php://memory');

        $request = new Request($baseRequest);
        $this->assertSame($baseRequest, $request->getOriginalRequest());
    }

    public function testCallingSettersRetainsOriginalRequest()
    {
        $url = 'http://example.com/foo';
        $baseRequest = new PsrRequest([], [], $url, 'GET', 'php://memory');

        $request = new Request($baseRequest);
        $request = $request->withMethod('POST');
        $new     = $request->withAddedHeader('X-Foo', 'Bar');

        $this->assertNotSame($request, $new);
        $this->assertNotSame($baseRequest, $new);
        $this->assertNotSame($baseRequest, $new->getCurrentRequest());
        $this->assertSame($baseRequest, $new->getOriginalRequest());
    }

    public function testCanAccessOriginalRequest()
    {
        $this->assertSame($this->original, $this->request->getOriginalRequest());
    }

    public function testDecoratorProxiesToAllMethods()
    {
        $stream = $this->getMock('Psr\Http\Message\StreamInterface');
        $psrRequest = new PsrRequest([], [], 'http://example.com', 'POST', $stream, [
            'Accept' => 'application/xml',
            'X-URL' => 'http://example.com/foo',
        ]);
        $request = new Request($psrRequest);

        $this->assertEquals('1.1', $request->getProtocolVersion());
        $this->assertSame($stream, $request->getBody());
        $this->assertSame($psrRequest->getHeaders(), $request->getHeaders());
        $this->assertEquals($psrRequest->getRequestTarget(), $request->getRequestTarget());
    }
}
