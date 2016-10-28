<?php

namespace SiteToolTest;

use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\SocketException;
use Amp\Artax\Response;
use FluentDOM\Document;
use FluentDOM\Element;

//U+2019	’	e2 80 99	RIGHT SINGLE QUOTATION MARK



class ParsingTest extends \SiteToolTest\BaseTestCase
{

    public function createData()
    {
    $html = <<< HTML

<html>
<body>
<a href="/s’t"><span data-quickedit-field-id="node/138299556/title/en/card_three" class="field field--name-title field--type-string field--label-hidden">Buyer’s guide: Ultra HD 4K TV</span>
</a>


</body>
</html>
HTML;

        return $html;
    }


    public function testParsing() 
    {
        $body = $this->createData();

        $document = new Document();
        $body = mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8');
        $document->loadHTML($body);
        $linkClosure = function (Element $element)  {
            $href = $element->getAttribute('href');
            $this->assertEquals(4, mb_strlen($href));
        };
        $document->find('//a')->each($linkClosure);
    }
    
}
