<?php

namespace uzgent\HideCustomApplicationLinks;

class HideCustomApplicationLinks extends \ExternalModules\AbstractExternalModule {

    public function __construct() {
        parent::__construct();
    }
    
    public function redcap_module_system_enable(string $version) {
        $this->setSystemSetting('enabled', true);
    }

    public function redcap_every_page_top(int $project_id) {
        if (defined('USERID')) {
            $user = $this->framework->getUser();
            $userRights = $user->getRights();
            if (!$user->isSuperUser() && !$userRights['design']) {
                $urlScript = $this->getUrl('js/hide_custom_application_links.js');
                ?>
                <script type="text/javascript" src="<?php echo $urlScript ?>"></script>
                <?php
            }
        }
    }
}
