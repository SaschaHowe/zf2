<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Form
 */

namespace ZendTest\Form\View\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\View\HelperConfiguration;
use Zend\Form\View\Helper\FormElement as FormElementHelper;
use Zend\View\Helper\Doctype;
use Zend\View\Renderer\PhpRenderer;

/**
 * @category   Zend
 * @package    Zend_Form
 * @subpackage UnitTest
 */
class FormElementTest extends TestCase
{
    public $helper;
    public $renderer;

    public function setUp()
    {
        $this->helper = new FormElementHelper();

        Doctype::unsetDoctypeRegistry();

        $this->renderer = new PhpRenderer;
        $helpers = $this->renderer->getHelperPluginManager();
        $config  = new HelperConfiguration();
        $config->configureServiceManager($helpers);

        $this->helper->setView($this->renderer);
    }

    public function getInputElements()
    {
        return array(
            array('text'),
            array('password'),
            array('checkbox'),
            array('radio'),
            array('submit'),
            array('reset'),
            array('file'),
            array('hidden'),
            array('image'),
            array('button'),
            array('number'),
            array('range'),
            array('date'),
            array('color'),
            array('search'),
            array('tel'),
            array('email'),
            array('url'),
            array('datetime'),
            array('datetime-local'),
            array('month'),
            array('week'),
            array('time'),
        );
    }

    /**
     * @dataProvider getInputElements
     */
    public function testRendersExpectedInputElement($type)
    {
        if ($type === 'radio') {
            $element = new Element\Radio('foo');
        } elseif ($type === 'checkbox') {
            $element = new Element\Checkbox('foo');
        } else {
            $element = new Element('foo');
        }

        $element->setAttribute('type', $type);
        $element->setAttribute('options', array('option' => 'value'));
        $element->setAttribute('src', 'http://zend.com/img.png');
        $markup  = $this->helper->render($element);

        $this->assertContains('<input', $markup);
        $this->assertContains('type="' . $type . '"', $markup);
    }

    public function getMultiElements()
    {
        return array(
            array('radio', 'input', 'type="radio"'),
            array('multi_checkbox', 'input', 'type="checkbox"'),
            array('select', 'option', '<select'),
        );
    }

    /**
     * @dataProvider getMultiElements
     * @group multi
     */
    public function testRendersMultiElementsAsExpected($type, $inputType, $additionalMarkup)
    {
        $element = new Element\MultiCheckbox('foo');
        $element->setAttribute('type', $type);
        $element->setAttribute('options', array(
            'value1' => 'option',
            'value2' => 'label',
            'value3' => 'last',
        ));
        $element->setAttribute('value', 'value2');
        $markup  = $this->helper->render($element);

        $this->assertEquals(3, substr_count($markup, '<' . $inputType), $markup);
        $this->assertContains($additionalMarkup, $markup);
        if ($type == 'select') {
            $this->assertRegexp('#value="value2"[^>]*?(selected="selected")#', $markup);
        }
    }

    public function testRendersCaptchaAsExpected()
    {
        $captcha = new Captcha\Dumb(array(
            'sessionClass' => 'ZendTest\Captcha\TestAsset\SessionContainer',
        ));
        $element = new Element\Captcha('foo');
        $element->setCaptcha($captcha);
        $markup = $this->helper->render($element);

        $this->assertContains($captcha->getLabel(), $markup);
    }

    public function testRendersCsrfAsExpected()
    {
        $element   = new Element\Csrf('foo');
        $inputSpec = $element->getInputSpecification();
        $hash = '';

        foreach ($inputSpec['validators'] as $validator) {
            $class = get_class($validator);
            switch ($class) {
                case 'Zend\Validator\Csrf':
                    $hash = $validator->getHash();
                    break;
                default:
                    break;
            }
        }

        $markup    = $this->helper->render($element);

        $this->assertRegexp('#<input[^>]*(type="hidden")#', $markup);
        $this->assertRegexp('#<input[^>]*(value="' . $hash . '")#', $markup);
    }

    public function testRendersTextareaAsExpected()
    {
        $element = new Element('foo');
        $element->setAttribute('type', 'textarea');
        $element->setAttribute('value', 'Initial content');
        $markup  = $this->helper->render($element);

        $this->assertContains('<textarea', $markup);
        $this->assertContains('>Initial content<', $markup);
    }

    public function testInvokeWithNoElementChainsHelper()
    {
        $element = new Element('foo');
        $this->assertSame($this->helper, $this->helper->__invoke());
    }
}
