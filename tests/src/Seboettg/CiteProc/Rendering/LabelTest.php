<?php
/**
 * citeproc-php
 *
 * @link        http://github.com/seboettg/citeproc-php for the source repository
 * @copyright   Copyright (c) 2016 Sebastian Böttger.
 * @license     https://opensource.org/licenses/MIT
 */

namespace Seboettg\CiteProc\Rendering;


use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\Context;
use Seboettg\CiteProc\Locale\Locale;
use Seboettg\CiteProc\TestSuiteTestCaseTrait;

class LabelTest extends \PHPUnit_Framework_TestCase
{
    use TestSuiteTestCaseTrait;


    public function testLabelEditorTranslator()
    {
        //TODO: implement
        //$this->_testRenderTestSuite("label_EditorTranslator");
    }

    public function testLabelEmptyLabelVanish()
    {
        $this->_testRenderTestSuite("label_EmptyLabelVanish");
    }

    public function testLabelImplicitForm()
    {
        $this->_testRenderTestSuite("label_Implicit");
    }

    public function testLabelNoFirstCharCap()
    {
        $this->_testRenderTestSuite("label_NoFirstCharCap");
    }

    public function testLabelNonexistentNameVariableLabel()
    {
        $this->_testRenderTestSuite("label_NonexistentNameVariableLabel");
    }

    public function testLabelPluralPagesWithAlphaPrefix()
    {
        $this->_testRenderTestSuite("label_PluralPagesWithAlphaPrefix");
    }
}
