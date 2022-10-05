<?php

namespace Fab\Vidi\Persistence;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;

/**
 * Class ConstraintContainer
 */
class ConstraintContainer
{
    /**
     * @var ConstraintInterface
     */
    protected $constraint;

    /**
     * @return ConstraintInterface
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * @param ConstraintInterface $constraint
     * @return $this
     */
    public function setConstraint($constraint)
    {
        $this->constraint = $constraint;
        return $this;
    }
}
