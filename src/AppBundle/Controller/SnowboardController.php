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
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace AppBundle\Controller;

use AppBundle\Website\LinkGenerator\SnowboardLinkGenerator;
use AppBundle\Website\Navigation\BreadcrumbHelperService;
use Pimcore\Model\DataObject\SnowboardLocations;
use Pimcore\Templating\Helper\HeadTitle;
use Pimcore\Templating\Helper\Placeholder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Paginator\Paginator;

class SnowboardController extends BaseController
{
    const SNOWBOARD_DEFAULT_DOCUMENT_PROPERTY_NAME = 'snowboard_default_document';

    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    public function listingAction(Request $request)
    {

        // get a list of snowboard objects and order them by title
        $snowboardList = new SnowboardLocations\Listing();
        $snowboardList->setOrderKey('title');
        $snowboardList->setOrder('ASC');

        $paginator = new Paginator($snowboardList);
        $paginator->setCurrentPageNumber($request->get('page'));
        $paginator->setItemCountPerPage(6);

        return [
            'snowboard'           => $paginator,
            'paginationVariables' => $paginator->getPages('Sliding'),
        ];
    }

    /**
     * @Route("Snowboard/{title}~n{snowboard}", name="snowboard-detail", defaults={"path"=""},
     *     requirements={"path"=".*?", "title"="[\w-]+", "snowboard"="\d+"})
     *
     * @param Request $request
     * @param HeadTitle $headTitleHelper
     * @param Placeholder $placeholderHelper
     * @param SnowboardLinkGenerator $snowboardLinkGenerator
     *
     * @return array
     */
    public function detailAction(
        Request $request,
        HeadTitle $headTitleHelper,
        Placeholder $placeholderHelper,
        SnowboardLinkGenerator $snowboardLinkGenerator
    )
    {
        $snowboard = SnowboardLocations::getById($request->get('snowboard'));

        if (!($snowboard instanceof SnowboardLocations && ($snowboard->isPublished() ||
                $this->verifyPreviewRequest($request, $snowboard)))) {
            throw new NotFoundHttpException('Snowboard not found.');
        }

        $headTitleHelper($snowboard->getTitle());

        $placeholderHelper('canonical')->set(
            $snowboardLinkGenerator->generate(
                $snowboard, [
                              'document' => $this->document->getProperty(self::SNOWBOARD_DEFAULT_DOCUMENT_PROPERTY_NAME),
                          ]
            )
        );

        return [
            'snowboard' => $snowboard,
        ];
    }
}
