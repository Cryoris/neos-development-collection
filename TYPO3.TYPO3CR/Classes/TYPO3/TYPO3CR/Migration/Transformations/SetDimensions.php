<?php
namespace TYPO3\TYPO3CR\Migration\Transformations;

/*
 * This file is part of the TYPO3.TYPO3CR package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeDimension;
use TYPO3\TYPO3CR\Domain\Repository\ContentDimensionRepository;

/**
 * Set dimensions on a node. This always overwrites existing dimensions, if you need to
 * add to existing dimensions, @see AddDimensions
 */
class SetDimensions extends AbstractTransformation
{
    /**
     * @Flow\Inject
     * @var ContentDimensionRepository
     */
    protected $contentDimensionRepository;

    /**
     * If you omit a configured dimension this transformation will set the default value for that dimension.
     *
     * @var array
     */
    protected $dimensionValues = array();

    /**
     * Sets the default dimension values for all dimensions that were not given.
     *
     * @var boolean
     */
    protected $addDefaultDimensionValues = true;

    /**
     * @param array $dimensionValues
     */
    public function setDimensionValues($dimensionValues)
    {
        $this->dimensionValues = $dimensionValues;
    }

    /**
     * @param boolean $addDefaultDimensionValues
     */
    public function setAddDefaultDimensionValues($addDefaultDimensionValues)
    {
        $this->addDefaultDimensionValues = $addDefaultDimensionValues;
    }

    /**
     * Change the property on the given node.
     *
     * @param \TYPO3\TYPO3CR\Domain\Model\NodeData $node
     * @return void
     */
    public function execute(\TYPO3\TYPO3CR\Domain\Model\NodeData $node)
    {
        $dimensions = array();
        foreach ($this->dimensionValues as $dimensionName => $dimensionConfiguration) {
            foreach ($dimensionConfiguration as $dimensionValues) {
                if (is_array($dimensionValues)) {
                    foreach ($dimensionValues as $dimensionValue) {
                        $dimensions[] = new NodeDimension($node, $dimensionName, $dimensionValue);
                    }
                } else {
                    $dimensions[] = new NodeDimension($node, $dimensionName, $dimensionValues);
                }
            }
        }

        if ($this->addDefaultDimensionValues === true) {
            $configuredDimensions = $this->contentDimensionRepository->findAll();
            foreach ($configuredDimensions as $configuredDimension) {
                if (!isset($this->dimensionValues[$configuredDimension->getIdentifier()])) {
                    $dimensions[] = new NodeDimension($node, $configuredDimension->getIdentifier(), $configuredDimension->getDefault());
                }
            }
        }

        $node->setDimensions($dimensions);
    }
}
