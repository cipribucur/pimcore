<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace AppBundle\Twig\Extension;

use AppBundle\Website\LinkGenerator\SnowboardLinkGenerator;
use Pimcore\Model\DataObject\SnowboardLocations;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SnowboardExtension extends AbstractExtension
{
    /**
     * @var SnowboardLinkGenerator
     */
    protected $snowboardLinkGenerator;

    /**
     * SnowboardExtension constructor.
     *
     * @param SnowboardLinkGenerator $snowboardLinkGenerator
     */
    public function __construct(SnowboardLinkGenerator $snowboardLinkGenerator)
    {
        $this->snowboardLinkGenerator = $snowboardLinkGenerator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('app_snowboard_detaillink', [$this, 'generateLink']),
        ];
    }

    /**
     * @param SnowboardLocations $snowboard
     *
     * @return string
     */
    public function generateLink(SnowboardLocations $snowboard): string
    {
        return $this->snowboardLinkGenerator->generate($snowboard, []);
    }
}
