<?php
namespace ZfcDatagridTest\Renderer\JqGrid\View\Helper;

use Exception;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Filter;
use ZfcDatagrid\Renderer\JqGrid\View\Helper;

/**
 * @group Renderer
 * @covers \ZfcDatagrid\Renderer\JqGrid\View\Helper\Columns
 */
class ColumnsTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\View\HelperPluginManager */
    private $sm;

    /**
     *
     * @var \ZfcDatagrid\Column\AbstractColumn
     */
    private $myCol;

    public function setUp(): void
    {
        $this->sm = $this->getMockBuilder(\Laminas\View\HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $myCol = $this->getMockForAbstractClass(\ZfcDatagrid\Column\AbstractColumn::class);
        $myCol->setUniqueId('myCol');

        $this->myCol = $myCol;
    }

    public function testSimple()
    {
        $helper = new Helper\Columns();

        $cols = [
            clone $this->myCol,
        ];

        $result = $helper($cols);

        $this->assertStringStartsWith('[{name:', $result);
        $this->assertStringEndsWith('"clearSearch":false}}]', $result);
    }

    public function testStyleBold()
    {
        $helper = new Helper\Columns();

        $col1 = clone $this->myCol;
        $col1->addStyle(new Style\Bold());
        $cols = [
            $col1,
        ];

        $result = $helper($cols);

        $this->assertStringStartsWith('[{name:', $result);
        $this->assertStringEndsWith(
            '<span style="font-weight: bold;">\' + cellvalue + \'</span>\'; ' .
            'return cellvalue; },searchoptions: {"clearSearch":false}}]',
            $result
        );
    }

    public function testStyleItalic()
    {
        $helper = new Helper\Columns();

        $col1 = clone $this->myCol;
        $col1->addStyle(new Style\Italic());
        $cols = [
            $col1,
        ];

        $result = $helper($cols);

        $this->assertStringStartsWith('[{name:', $result);
        $this->assertStringEndsWith(
            '<span style="font-style: italic;">\' + cellvalue + \'</span>\'; ' .
            'return cellvalue; },searchoptions: {"clearSearch":false}}]',
            $result
        );
    }

    public function testStyleColor()
    {
        $helper = new Helper\Columns();

        $col1 = clone $this->myCol;
        $col1->addStyle(new Style\Color(Style\Color::RED));
        $cols = [
            $col1,
        ];

        $result = $helper($cols);

        $this->assertStringStartsWith('[{name:', $result);
        $this->assertStringEndsWith(
            '<span style="color: #ff0000;">\' + cellvalue + \'</span>\'; ' .
            'return cellvalue; },searchoptions: {"clearSearch":false}}]',
            $result
        );
    }

    public function testStyleBackgroundColor()
    {
        $helper = new Helper\Columns();

        $col1 = clone $this->myCol;
        $col1->addStyle(new Style\BackgroundColor(Style\BackgroundColor::RED));
        $cols = [
            $col1,
        ];

        $result = $helper($cols);

        $this->assertStringStartsWith('[{name:', $result);
        $this->assertStringEndsWith('search: true,searchoptions: {"clearSearch":false}}]', $result);
    }

    public function testStyleException()
    {
        $styleMock = $this->getMockForAbstractClass(\ZfcDatagrid\Column\Style\AbstractStyle::class);
        $helper    = new Helper\Columns();

        $col1 = clone $this->myCol;
        $col1->addStyle($styleMock);
        $cols = [
            $col1,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Not defined style: \"[a-zA-Z0-9_]+\"/');

        $helper($cols);
    }

    public function testStyleByValueEqual()
    {
        $helper = new Helper\Columns();

        $col1 = clone $this->myCol;

        $style = new Style\Bold();
        $style->addByValue($col1, 123);

        $col1->addStyle($style);
        $cols = [
            $col1,
        ];

        $result = $helper($cols);

        $this->assertStringStartsWith('[{name:', $result);
        $this->assertStringEndsWith(
            'if (rowObject.myCol == \'123\') {cellvalue = \'<span style="font-weight: bold;">' .
            '\' + cellvalue + \'</span>\';} return cellvalue; },searchoptions: {"clearSearch":false}}]',
            $result
        );
    }

    public function testCSSClassCell()
    {
        $helper = new Helper\Columns();

        $col1 = clone $this->myCol;
        $col1->addStyle(new Style\CSSClass('test-class'));
        $cols = [
            $col1,
        ];

        $result = $helper($cols);

        $this->assertStringStartsWith('[{name:', $result);
        $this->assertStringContainsString('<span class="test-class">\' + cellvalue + \'</span>\';', $result);
    }

    public function testStyleByValueNotEqual()
    {
        $helper = new Helper\Columns();

        $col1 = clone $this->myCol;

        $style = new Style\Bold();
        $style->addByValue($col1, 123, Filter::NOT_EQUAL);

        $col1->addStyle($style);
        $cols = [
            $col1,
        ];

        $result = $helper($cols);

        $this->assertStringStartsWith('[{name:', $result);
        $this->assertStringEndsWith(
            'if (rowObject.myCol != \'123\') {cellvalue = \'<span style="font-weight: bold;">' .
            '\' + cellvalue + \'</span>\';} return cellvalue; },searchoptions: {"clearSearch":false}}]',
            $result
        );
    }

    public function testStyleByValueNotSupported()
    {
        $helper = new Helper\Columns();

        $col1 = clone $this->myCol;

        $style = new Style\Bold();
        $style->addByValue($col1, 123, Filter::IN);

        $col1->addStyle($style);
        $cols = [
            $col1,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Currently not supported filter operation: "=(%s)"');
        $result = $helper($cols);
    }

    public function testTranslate()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\ServiceManager\ServiceManager $sm */
        $sm = $this->getMockBuilder(\Laminas\ServiceManager\ServiceManager::class)
            ->setMethods(null)
            ->getMock();

        $helper = new Helper\Columns();

        $reflection = new \ReflectionClass($helper);
        $method     = $reflection->getMethod('translate');
        $method->setAccessible(true);

        $result = $method->invokeArgs($helper, ['test']);

        $this->assertEquals('test', $result);
    }

    public function testTranslateWithMockedTranslator()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\I18n\Translator\Translator $translator */
        $translator = $this->getMockBuilder(\Laminas\I18n\Translator\Translator::class)
            ->disableOriginalConstructor()
            ->setMethods(['translate'])
            ->getMock();

        // Configure the stub.
        $translator->method('translate')
            ->will($this->returnValueMap([
                ['test', 'default', null, 'translate'],
            ]));

        $helper = new Helper\Columns();
        $helper->setTranslator($translator);

        $reflection = new \ReflectionClass($helper);
        $method     = $reflection->getMethod('translate');
        $method->setAccessible(true);

        $result = $method->invokeArgs($helper, ['test']);

        $this->assertEquals('translate', $result);
    }
}
