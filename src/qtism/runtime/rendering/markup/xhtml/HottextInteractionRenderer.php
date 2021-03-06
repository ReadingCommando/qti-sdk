<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\rendering\markup\xhtml;

use qtism\data\QtiComponent;
use \DOMDocumentFragment;

/**
 * HottextInteraction renderer. Rendered components will be transformed as
 * 'div' elements with the 'qti-hottextInteraction' and 'qti-blockInteraction'
 * additional CSS classes.
 *
 * The following data-X attributes will be rendered:
 *
 * * data-responseIdentifier = qti:interaction->responseIdentifier
 * * data-maxChoices = qti:hottextInteraction
 * * data-minChoices = qti:hottextInteraction
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class HottextInteractionRenderer extends InteractionRenderer
{
    /**
     * @see \qtism\runtime\rendering\markup\xhtml\InteractionRenderer::appendAttributes()
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = '')
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-blockInteraction');
        $this->additionalClass('qti-hottextInteraction');

        $fragment->firstChild->setAttribute('data-max-choices', $component->getMaxChoices());
        $fragment->firstChild->setAttribute('data-min-choices', $component->getMinChoices());
    }
}
