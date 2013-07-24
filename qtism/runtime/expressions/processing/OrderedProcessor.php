<?php

namespace qtism\runtime\expressions\processing;

use qtism\runtime\common\Utils as CommonUtils;
use qtism\runtime\common\OrderedContainer;
use qtism\data\expressions\Expression;
use qtism\data\expressions\operators\Ordered;
use \InvalidArgumentException;

/**
 * The OrderedProcessor class aims at processing Ordered QTI Data Model Expression objects.
 * 
 * From IMS QTI:
 * 
 * The ordered operator takes 0 or more sub-expressions all of which must have 
 * either single or ordered cardinality. Although the sub-expressions may be of 
 * any base-type they must all be of the same base-type. The result is a 
 * container with ordered cardinality containing the values of the 
 * sub-expressions, sub-expressions with ordered cardinality have their 
 * individual values added (in order) to the result: contains cannot 
 * contain other containers. For example, when applied to A, B, {C,D} 
 * the ordered operator results in {A,B,C,D}. Note that the ordered 
 * operator never results in an empty container. All sub-expressions 
 * with NULL values are ignored. If no sub-expressions are given 
 * (or all are NULL) then the result is NULL
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OrderedProcessor extends OperatorProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Ordered) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The OrderedProcessor class only accepts Ordered QTI Data Model Expression objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the current expression.
	 * 
	 * @return OrderedContainer|null An OrderedContainer object or NULL.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
		$operands = $this->getOperands();
		
		if (count($operands) === 0) {
			return null;
		}
		
		if ($operands->exclusivelySingleOrOrdered() === false) {
			$msg = "The Ordered operator only accepts operands with single or ordered cardinality.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$refType = null;
		$returnValue = null;
		
		foreach ($operands as $operand) {
			
			if (is_null($operand) || ($operand instanceof OrderedContainer && $operand->isNull())) {
				// As per specs, ignore.
				continue;
			}
			else {
				if ($refType !== null) {
					// A reference type as already been identifier.
					if (CommonUtils::inferBaseType($operand) === $refType) {
						// $operand can be added to $returnValue.
						static::appendValue($returnValue, $operand);
					}
					else {
						// baseType mismatch.
						$msg = "The Ordered operator only accepts values with a similar baseType.";
						throw new ExpressionProcessingException($msg, $this);
					}
				}
				else if (($discoveryType = CommonUtils::inferBaseType($operand)) !== false) {
					// First value being identified as non-null.
					$refType = $discoveryType;
					$returnValue = new OrderedContainer($refType);
					static::appendValue($returnValue, $operand);
				}
			}
		}
		
		return $returnValue;
	}
	
	/**
	 * Append a value (An orderedContainer or a primitive datatype) to a given $container.
	 * 
	 * @param OrderedContainer $container An OrderedContainer object you want to append something to.
	 * @param scalar|OrderedContainer $value A value to append to the $container. 
	 */
	protected static function appendValue(OrderedContainer $container, $value) {
		if ($value instanceof OrderedContainer) {
			foreach ($value as $v) {
				$container[] = $v;
			}
		}
		else {
			// primitive type.
			$container[] = $value;
		}
	}
}