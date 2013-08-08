<?php

namespace qtism\runtime\processing;

use qtism\runtime\rules\RuleProcessorFactory;
use qtism\data\QtiComponent;
use qtism\runtime\common\ProcessingException;
use qtism\data\processing\OutcomeProcessing;
use qtism\runtime\common\State;
use qtism\runtime\common\AbstractEngine;
use \InvalidArgumentException;

/**
 * The OutcomeProcessingEngine class aims at providing a single engine to execute
 * any OutcomeProcessing object.
 * 
 * If the given State object given as the context is an AssessmentTestContext object,
 * all the test-level outcome variables held by this context will be reset to their
 * defaults prior to carrying out the instructions described by the outcomeRules of the
 * OutcomeProcessing.
 * 
 * FROM IMS QTI:
 * 
 * Outcome processing takes place each time the candidate submits the responses for an 
 * item (when in individual submission mode) or a group of items (when in simultaneous 
 * submission mode). It happens after any (item level) response processing triggered by 
 * the submission. The values of the test's outcome variables are always reset to their 
 * defaults prior to carrying out the instructions described by the outcomeRules. Because 
 * outcome processing happend each time the candidate submits responses the resulting values 
 * of the test-level outcomes may be used to activate test-level feedback during the test or 
 * to control the behaviour of subsequent parts through the use of preConditions and 
 * branchRules.
 * 
 * The structure of outcome processing is similar to that or responseProcessing.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeProcessingEngine extends AbstractEngine {
	
	/**
	 * The factory to be used to retrieve
	 * the relevant processor to a given rule.
	 * 
	 * @var RuleProcessorFactory
	 */
	private $ruleProcessorFactory;
	
	/**
	 * Create a new OutcomeProcessingEngine object.
	 * 
	 * @param QtiComponent $outcomeProcessing A QTI Data Model OutcomeProcessing object. 
	 * @param State $context A State object as the execution context.
	 */
	public function __construct(QtiComponent $outcomeProcessing, State $context = null) {
	    parent::__construct($outcomeProcessing, $context);
	    $this->setRuleProcessorFactory(new RuleProcessorFactory());
	}
	
	/**
	 * Set the OutcomeProcessing object to be executed by the engine depending
	 * on the current context.
	 * 
	 * @param QtiComponent $outcomeProcessing An OutcomeProcessing object.
	 * @throws InvalidArgumentException If $outcomeProcessing is not An OutcomeProcessing object.
	 */
	public function setComponent(QtiComponent $outcomeProcessing) {
		if ($outcomeProcessing instanceof OutcomeProcessing) {
			parent::setComponent($outcomeProcessing);
		}
		else {
			$msg = "The OutcomeProcessingEngine class only accepts OutcomeProcessing objects to be executed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Set the factory to be used to get the relevant rule processors.
	 * 
	 * @param RuleProcessorFactory $ruleProcessorFactory A RuleProcessorFactory object.
	 */
	public function setRuleProcessorFactory(RuleProcessorFactory $ruleProcessorFactory) {
	    $this->ruleProcessorFactory = $ruleProcessorFactory;
	}
	
	/**
	 * Get the factory to be used to get the relevant rule processors.
	 * 
	 * @return RuleProcessorFactory A RuleProcessorFactory object.
	 */
	public function getRuleProcessorFactory() {
	    return $this->ruleProcessorFactory;
	}
	
	/**
	 * Execute the OutcomeProcessing object depending on the current context.
	 * 
	 * The following sub-types of ProcessingException may be thrown:
	 * 
	 * * RuleProcessingException: if an error occurs while executing an OutcomeRule inside the OutcomeProcessing OR if the ExitTest rule is invoked. In this last case, a specific error code will be produced to handle the situation accordingly.
	 * * ExpressionProcessingException: if an error occurs while executing an expression, inside a rule that belongs to the OutcomeProcessing.
	 * 
	 * @throws ProcessingException If an error occurs while executing the OutcomeProcessing.
	 */
	public function process() {
		// @todo If given $context is an AssessmentTestContext, reset all
		// test-level outcome variables (except duration !!!!).
		$context = $this->getContext();
		$outcomeProcessing = $this->getComponent();
	    foreach ($outcomeProcessing->getOutcomeRules() as $rule) {
	        $processor = $this->getRuleProcessorFactory()->createProcessor($rule);
	        $processor->setState($context);
	        $processor->process();
	        $this->trace($rule->getQtiClassName() . ' executed');
	    }
	}
} 