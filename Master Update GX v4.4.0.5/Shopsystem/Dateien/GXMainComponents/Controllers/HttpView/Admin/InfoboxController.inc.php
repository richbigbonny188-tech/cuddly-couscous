<?php
/* --------------------------------------------------------------
   InfoboxController.inc.php 2020-06-17
   Gambio GmbH
   http://www.gambio.de
   Copyright © 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class InfoboxController
 */
class InfoboxController extends AdminHttpViewController
{
    /**
     * @var AdminInfoboxControl
     */
    protected $infoboxControl;
    
    
    /**
     * Initialization of VPE controller
     */
    public function init()
    {
        $this->infoboxControl = MainFactory::create_object('AdminInfoboxControl');
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionGetMessages(): JsonHttpControllerResponse
    {
        $messages     = [];
        $messagesData = $this->infoboxControl->get_all_messages();
        foreach ($messagesData as $messageData) {
            $messages[] = [
                'id'          => (int)$messageData['infobox_messages_id'],
                'status'      => $messageData['status'],
                'type'        => $messageData['type'],
                'headline'    => $messageData['headline'],
                'text'        => $messageData['message'],
                'buttonLabel' => $messageData['button_label'],
                'buttonUrl'   => $messageData['button_link'],
                'hideable'    => $messageData['visibility'] === 'hideable'
                                 || $messageData['visibility'] === 'removable',
                'removable'   => $messageData['visibility'] === 'removable',
            
            ];
        }
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => true, 'messages' => $messages]);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionMarkAsRead(): JsonHttpControllerResponse
    {
        $id      = (int)$this->_getQueryParameter('id');
        $success = $this->infoboxControl->set_status($id, 'read');
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => $success]);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionMarkAsHidden(): JsonHttpControllerResponse
    {
        $id      = (int)$this->_getQueryParameter('id');
        $success = $this->infoboxControl->set_status($id, 'hidden');
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => $success]);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionMarkAsRemoved(): JsonHttpControllerResponse
    {
        $id      = (int)$this->_getQueryParameter('id');
        $success = $this->infoboxControl->set_status($id, 'deleted');
        
        return MainFactory::create('JsonHttpControllerResponse', ['success' => $success]);
    }
}