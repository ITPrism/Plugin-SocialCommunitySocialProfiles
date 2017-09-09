<?php
/**
 * @package      Social Community
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Social Community social profiles plugin
 *
 * @package      Social Community
 * @subpackage   Plugins
 */
class plgContentScsocialprofiles extends JPlugin
{
    /**
     * Prepare content that will be displayed after user profile data.
     *
     * @param string  $context
     * @param stdClass $item
     * @param Joomla\Registry\Registry $params
     *
     * @return null|string
     * @throws Exception
     */
    public function onContentBeforeDisplayProfile($context, &$item, &$params)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        if (strcmp('com_socialcommunity.profile.details', $context) !== 0) {
            return null;
        }

        // Load language
        $this->loadLanguage();

        $databaseRequest = new \Prism\Database\Request\Request();
        $databaseRequest
            ->addCondition(new \Prism\Database\Condition\Condition(['column' => 'user_id', 'value' => $item->user_id]))
            ->requestField(new \Prism\Database\Request\Field(['column' => 'link']))
            ->requestField(new \Prism\Database\Request\Field(['column' => 'service']));

        $mapper         = new \Socialcommunity\Socialprofile\Mapper(new \Socialcommunity\Socialprofile\Gateway\JoomlaGateway(JFactory::getDbo()));
        $repository     = new \Socialcommunity\Socialprofile\Repository($mapper);
        $socialProfiles = $repository->fetchCollection($databaseRequest);

        $sprofiles = array();

        /** @var \Socialcommunity\Socialprofile\Socialprofile $sprofile */
        foreach ($socialProfiles as $sprofile) {
            $sprofiles[$sprofile->getService()] = $sprofile->getLink();
        }

        $output = array();

        if ($item->website !== '' && $this->params->get('display_website', Prism\Constants::YES)) {
            $output[] = '<a href="'.htmlentities($item->website, ENT_QUOTES, 'UTF-8').'" class="sc-website-link" target="_blank">';
            $output[] = JHtmlString::truncate($item->website, 32, true, false);
            $output[] = '</a>';
        }

        if (array_key_exists('facebook', $sprofiles) && $this->params->get('display_facebook', Prism\Constants::YES)) {
            $output[] = '<a href="' . htmlentities($sprofiles['facebook'], ENT_QUOTES, 'UTF-8') . '" class="sc-socialprofile-link" target="_blank">';
            $output[] = '<img src="media/com_socialcommunity/images/facebook_32x32.png" alt="' . JText::sprintf('PLG_CONTENT_SCSOCIALPROFILES_SOCIAL_PROFILE_ALT', 'Facebook', htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8')) . '" width="32" height="32" />';
            $output[] = '</a>';
        }

        if (array_key_exists('twitter', $sprofiles) && $this->params->get('display_twitter', Prism\Constants::YES)) {
            $output[] = '<a href="' .htmlentities($sprofiles['twitter'], ENT_QUOTES, 'UTF-8') . '" class="sc-socialprofile-link" target="_blank">';
            $output[] = '<img src="media/com_socialcommunity/images/twitter_32x32.png" alt="' . JText::sprintf('PLG_CONTENT_SCSOCIALPROFILES_SOCIAL_PROFILE_ALT', 'Twitter', htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8')) . '" width="32" height="32" />';
            $output[] = '</a>';
        }

        return implode("\n", $output);
    }
}
