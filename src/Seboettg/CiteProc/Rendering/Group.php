<?php
/*
 * citeproc-php
 *
 * @link        http://github.com/seboettg/citeproc-php for the source repository
 * @copyright   Copyright (c) 2016 Sebastian Böttger.
 * @license     https://opensource.org/licenses/MIT
 */

namespace Seboettg\CiteProc\Rendering;
use Seboettg\CiteProc\Styles\AffixesTrait;
use Seboettg\CiteProc\Styles\ConsecutivePunctuationCharacterTrait;
use Seboettg\CiteProc\Styles\DelimiterTrait;
use Seboettg\CiteProc\Styles\DisplayTrait;
use Seboettg\CiteProc\Util\Factory;
use Seboettg\Collection\ArrayList;


/**
 * Class Group
 * @package Seboettg\CiteProc\Rendering
 *
 * @author Sebastian Böttger <seboettg@gmail.com>
 */
class Group implements RenderingInterface, HasParent
{
    use DelimiterTrait,
        AffixesTrait,
        DisplayTrait,
        ConsecutivePunctuationCharacterTrait;

    const CLASS_PATH = 'Seboettg\CiteProc\Rendering';

    private static $suppressableElements = [
        self::CLASS_PATH . '\Number',
        self::CLASS_PATH . '\Group',
        self::CLASS_PATH . '\Date\Date'
    ]; 

    /**
     * @var ArrayList
     */
    private $children;

    /**
     * cs:group may carry the delimiter attribute to separate its child elements
     * @var
     */
    private $delimiter = "";

    private $parent;

    public function __construct(\SimpleXMLElement $node, $parent)
    {
        $this->parent = $parent;
        $this->children = new ArrayList();
        foreach ($node->children() as $child) {
            $this->children->append(Factory::create($child, $this));
        }
        $this->initDisplayAttributes($node);
        $this->initAffixesAttributes($node);
        $this->initDelimiterAttributes($node);
    }

    /**
     * @param $data
     * @param int|null $citationNumber
     * @return string
     */
    public function render($data, $citationNumber = null)
    {
        //$text = '';
        $textParts = array();
        $terms = $variables = $haveVariables = $elementCount = 0;
        foreach ($this->children as $child) {
            $elementCount++;
            if (($child instanceof Text) &&
                ($child->getSource() == 'term' ||
                    $child->getSource() == 'value')) {
                $terms++;
            }
            if (($child instanceof Label)) {
                ++$terms;
            }
            if (method_exists($child, "getSource") && $child->getSource() == 'variable' &&
                !empty($child->getVariable()) &&
                !empty($data->{$child->getVariable()})
            ) {
                ++$variables;
            }
            $text = $child->render($data, $citationNumber);
            $delimiter = $this->delimiter;
            if (!empty($text)) {
                if ($delimiter && ($elementCount < count($this->children))) {
                    //check to see if the delimiter is already the last character of the text string
                    //if so, remove it so we don't have two of them when we paste together the group
                    $stext = strip_tags(trim($text));
                    if ((strrpos($stext, $delimiter[0]) + 1) == strlen($stext) && strlen($stext) > 1) {
                        $text = str_replace($stext, '----REPLACE----', $text);
                        $stext = substr($stext, 0, -1);
                        $text = str_replace('----REPLACE----', $stext, $text);
                    }
                }
                //give the text parts a name
                if ($child instanceof Text) {
                    $textParts[$child->getVariable()] = $text;
                } else {
                    $textParts[$elementCount] = $text;
                }


                if (method_exists($child, "getSource") && $child->getSource() == 'variable' || (method_exists($child, "getVariable") && !empty($child->getVariable()))) {
                    $haveVariables++;
                }

                if (method_exists($child, "getSource") && $child->getSource() == 'macro') {
                    $haveVariables++;
                }
            }
        }
        if (empty($textParts)) {
            return "";
        }
        if ($variables && !$haveVariables) {
            return ""; // there has to be at least one other none empty value before the term is output
        }

        if (count($textParts) == $terms) {
            return ""; // there has to be at least one other none empty value before the term is output
        }

        //$text = implode($delimiter, $textParts); // insert the delimiter if supplied.
        $text = implode($this->delimiter, $textParts);
        if (!empty($text)) {
            return $this->wrapDisplayBlock($this->addAffixes(($text)));
        }

        return "";
                /*
                $arr = new ArrayList();
                $i = 0;

                 cs:group implicitly acts as a conditional: cs:group and its child elements are suppressed if a) at least
                one rendering element in cs:group calls a variable (either directly or via a macro), and b) all variables
                that are called are empty. This accommodates descriptive cs:text elements.

                $suppressConditionA = false;
                /** @var RenderingInterface $child /
                foreach ($this->children as $child) {

                    $res = $child->render($data, $citationNumber);
                    $this->getChildsAffixesAndDelimiter($child);

                    if ($this->doesRenderingElementCallsVariable($child)) {
                        //$arr[] = $res;
                        $arr->add(get_class($child).$i, $res);
                        $suppressConditionA = true;
                    } else {
                        $arr[$i] = $res;
                    }
                    ++$i;
                }

                $suppressConditionB = null;

                if ($suppressConditionA) {
                    foreach ($arr as $key => $value) {
                        if (is_null($suppressConditionB)) {
                            $suppressConditionB = true;
                        }
                        if (!is_numeric($key)) {
                            $suppressConditionB = $suppressConditionB && empty($value);
                        }
                        if (!$suppressConditionB) {
                            break;
                        }
                    }
                }

                if ($suppressConditionA && !is_null($suppressConditionB) && $suppressConditionB === true) {
                    return "";
                }


                if (!empty($arr)) {
                    $res = $this->wrapDisplayBlock($this->addAffixes(implode($this->delimiter, $arr->toArray())));
                    $res = $this->removeConsecutiveChars($res);
                    return $res;
                }
                return "";
                */
    }


    private function doesRenderingElementCallsVariable($child)
    {
        if ($child instanceof Text) {
            if ($child->rendersVariable()) {
                return true;
            }
            return false;
        }
        if (in_array(get_class($child), self::$suppressableElements)) {

            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }
}
