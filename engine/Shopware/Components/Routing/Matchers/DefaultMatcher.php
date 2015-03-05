<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Routing\Matchers;

use Shopware\Components\Routing\MatcherInterface;
use Shopware\Components\Routing\Context;
use Enlight_Controller_Dispatcher_Default as EnlightDispatcher;

/**
 * @category  Shopware
 * @package   Shopware\Components\Routing
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class DefaultMatcher implements MatcherInterface
{
    /**
     * @var EnlightDispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $separator;

    /**
     * @param EnlightDispatcher $dispatcher
     * @param string $separator
     */
    public function __construct(EnlightDispatcher $dispatcher, $separator = '/')
    {
        $this->dispatcher = $dispatcher;
        $this->separator = $separator;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo, Context $context)
    {
        $path = trim($pathInfo, $this->separator);

        if (empty($path)) {
            return false;
        }

        $query = [];
        $params = [];
        foreach (explode($this->separator, $path) as $routePart) {
            $routePart = urldecode($routePart);
            if (empty($query[$context->getModuleKey()]) && $this->dispatcher->isValidModule($routePart)) {
                $query[$context->getModuleKey()] = $routePart;
            } elseif (empty($query[$context->getControllerKey()])) {
                $query[$context->getControllerKey()] = $routePart;
            } elseif (empty($query[$context->getActionKey()])) {
                $query[$context->getActionKey()] = $routePart;
            } else {
                $params[] = $routePart;
            }
        }

        $query[$context->getModuleKey()] = isset($query[$context->getModuleKey()])
            ? $query[$context->getModuleKey()]
            : $this->dispatcher->getDefaultModule();

        $query[$context->getControllerKey()] = isset($query[$context->getControllerKey()])
            ? $query[$context->getControllerKey()]
            : $this->dispatcher->getDefaultControllerName();

        $query[$context->getActionKey()] = isset($query[$context->getActionKey()])
            ? $query[$context->getActionKey()]
            : $this->dispatcher->getDefaultAction();

        if ($params) {
            $chunks = array_chunk($params, 2, false);
            foreach ($chunks as $chunk) {
                if (isset($chunk[1])) {
                    $query[$chunk[0]] = $chunk[1];
                } else {
                    $query[$chunk[0]] = '';
                }
            }
        }

        return $query;
    }
}