<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package
 */


namespace qtism\data\storage\xml\marshalling;

use qtism\data\content\interactions\Orientation;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use \DOMElement;
use \InvalidArgumentException;

/**
 * The Marshaller implementation for ChoiceInteraction elements of the content model.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ChoiceInteractionMarshaller extends ContentMarshaller {
    
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children) {
            
            if (($responseIdentifier = self::getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
                
                $fqClass = $this->lookupClass($element);
                $component = new $fqClass($responseIdentifier, new SimpleChoiceCollection($children->getArrayCopy()));
                
                if (($shuffle = self::getDOMElementAttributeAs($element, 'shuffle', 'boolean')) !== null) {
                    $component->setShuffle($shuffle);
                }
                
                if (($maxChoices = self::getDOMElementAttributeAs($element, 'maxChoices', 'integer')) !== null) {
                    $component->setMaxChoices($maxChoices);
                }
                
                if (($minChoices = self::getDOMElementAttributeAs($element, 'minChoices', 'integer')) !== null) {
                    $component->setMinChoices($minChoices);
                }
                
                if (($orientation = self::getDOMElementAttributeAs($element, 'orientation')) !== null) {
                    $component->setOrientation(Orientation::getConstantByName($orientation));
                }
                
                $promptElts = self::getChildElementsByTagName($element, 'prompt');
                if (count($promptElts) > 0) {
                    $promptElt = $promptElts[0];
                    $prompt = $this->getMarshallerFactory()->createMarshaller($promptElt)->unmarshall($promptElt);
                    $component->setPrompt($prompt);
                }
                
                self::fillBodyElement($component, $element);
                
                return $component;
            }
            else {
                $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'choiceInteraction' element.";
                throw new UnmarshallingException($msg, $element);
            }
    }
    
    protected function marshallChildrenKnown(QtiComponent $component, array $elements) {
        
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        self::fillElement($element, $component);
        self::setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());
        
        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }
        
        if ($component->mustShuffle() !== false) {
            self::setDOMElementAttribute($element, 'shuffle', true);
        }
        
        if ($component->getMaxChoices() !== 1) {
            self::setDOMElementAttribute($element, 'maxChoices', $component->getMaxChoices());
        }
        
        if ($component->getMinChoices() !== 0) {
            self::setDOMElementAttribute($element, 'minChoices', $component->getMinChoices());
        }
        
        if ($component->getOrientation() !== Orientation::VERTICAL) {
            self::setDOMElementAttribute($element, 'orientation', Orientation::getNameByConstant(Orientation::HORIZONTAL));
        }
        
        foreach ($elements as $e) {
            $element->appendChild($e);
        }
        
        return $element;
    }
    
    protected function setLookupClasses() {
        $this->lookupClasses = array("qtism\\data\\content\\interactions");
    }
}