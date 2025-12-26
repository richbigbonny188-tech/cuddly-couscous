<?php
/* --------------------------------------------------------------
   EmailsController.inc.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\PermissionService;
use Gambio\Core\AdminAccess\Role\PermissionAction;

MainFactory::load_class('AdminHttpViewController');

/**
 * Class EmailsController
 *
 * PHP controller that handles the admin/emails page operations. You can also use it to
 * perform email operations from JavaScript by providing encoded JSON arrays with email
 * information from other pages.
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class EmailsController extends AdminHttpViewController
{
    /**
     * Used core emails operations.
     *
     * @var EmailServiceInterface
     */
    protected $emailService;
    
    /**
     * Used for parsing and encoding operations.
     *
     * @var EmailParser
     */
    protected $emailParser;
    
    /**
     * Used for attachment files manipulation.
     *
     * @var AttachmentsHandler
     */
    protected $attachmentsHandler;
    
    
    /**
     * Initializes the controller.
     *
     * Perform the common operations before the parent class proceeds with the controller
     * method execution. In this case every method needs the EmailService so it is loaded
     * once before every method.
     *
     * @param HttpContextInterface $httpContext
     */
    public function proceed(HttpContextInterface $httpContext)
    {
        if (file_exists(DIR_FS_ADMIN . 'html/content/emails/emails.html') == false) {
            die('[error]' . trigger_error('template file not found', E_USER_ERROR));
        }
        $this->emailService       = StaticGXCoreLoader::getService('Email');
        $this->emailParser        = MainFactory::create('EmailParser', $this->emailService);
        $this->attachmentsHandler = MainFactory::create('AttachmentsHandler', DIR_FS_CATALOG . 'uploads');
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/');
        parent::proceed($httpContext); // proceed http context from parent class
    }
    
    
    /**
     * Displays the administration emails page.
     *
     * The administration page contains various JavaScript controllers which will make AJAX
     * requests to this class in order to get/store email information. Check the JavaScript
     * code of the page in the "admin/javascript/engine/controllers/emails" directory.
     *
     * @return AdminLayoutHttpControllerResponse
     *
     * @throws RuntimeException If page token generator was not found.
     * @throws InvalidArgumentException
     */
    public function actionDefault()
    {
        if (isset($_SESSION['coo_page_token']) == false) {
            // CSRF Protection
            throw new RuntimeException('Page Token Generator not found.');
        }
        
        $userConfigurationService = StaticGXCoreLoader::getService('UserConfiguration');
        $fileManagerConfiguration = MainFactory::create('ResponsiveFileManagerConfigurationStorage');
        
        // Admin Access Service
        /** @var PermissionService $adminAccessService */
        $adminAccessService = LegacyDependencyContainer::getInstance()->get(PermissionService::class);
        
        $lang     = MainFactory::create_object('LanguageTextManager', ['emails', $_SESSION['languages_id']]);
        $title    = $lang->get_text('emails');
        $template = 'emails/emails.html';
        $data     = [
            'pageToken'             => $_SESSION['coo_page_token']->generate_token(),
            'permissionsGranted'    => [
                                       'configurations' => $adminAccessService->checkAdminPermission((int)$_SESSION['customer_id'],
                                                                                                     PermissionAction::READ,
                                                                                                     GroupItem::PAGE_TYPE,
                                                                                                     'configuration.php'),
            ],
            'userId'                => (int)$_SESSION['customer_id'],
            'emailMultiDropdownBtn' => $userConfigurationService->getUserConfiguration(new IdType($_SESSION['customer_id']),
                                                                                       'emailMultiDropdownBtn'),
            'responsiveFileManager' => $fileManagerConfiguration->isInstalled()
                                       && $fileManagerConfiguration->get('use_in_email_pages')
        ];
        
        $assets        = [
            'admin_labels.lang.inc.php',
            'buttons.lang.inc.php',
            'db_backup.lang.inc.php',
            'emails.lang.inc.php',
            'lightbox_buttons.lang.inc.php',
            'messages.lang.inc.php',
        ];
        $subNavigation = [
            [
                'text'   => $lang->get_text('emails'),
                'link'   => '',
                'active' => true,
            ],
            [
                'text'   => $lang->get_text('BOX_CONFIGURATION_12', 'admin_menu'),
                'link'   => 'configurations?query=mail',
                'active' => false,
            ],
            [
                'text'   => $lang->get_text('BOX_GM_EMAILS', 'admin_menu'),
                'link'   => 'gm_emails.php',
                'active' => false,
            ],
        ];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($title,
                                                                                  $template,
                                                                                  $data,
                                                                                  $assets,
                                                                                  $subNavigation);
    }
    
    
    /**
     * [AJAX - GET] Server-side processing of the main emails table.
     *
     * The data returned by this method will be used by the main table of the page which
     * will display the emails records. DataTables will automatically make an AJAX request
     * and display the returned data.
     *
     * @link https://datatables.net/examples/ajax/objects.html
     * @link http://www.datatables.net/examples/server_side/simple.html
     *
     * @return JsonHttpControllerResponse Array that contains the table data.
     */
    public function actionDataTable()
    {
        try {
            // Filter Keyword
            $keyword = $_REQUEST['search']['value'];
            
            // Limit Records
            $limit = [
                'limit'  => (int)$_REQUEST['length'],
                'offset' => (int)$_REQUEST['start']
            ];
            
            // Order Rules
            $order = $this->_getTableOrder($_REQUEST['order'][0]);
            
            $emails = $this->emailService->filter($keyword, $limit, $order);
            
            if ($emails === null) {
                $emails = []; // No records found
            }
            
            // Prepare Table Data
            $tableData = [];
            $rowCount  = (int)$_REQUEST['start'];
            foreach ($emails->getArray() as $email) {
                $tableData[] = [
                    'DT_RowData'    => $this->emailParser->encodeEmail($email),
                    'row_count'     => ++$rowCount,
                    'creation_date' => $email->getCreationDate()->format('d.m.Y H:i'),
                    'sent_date'     => ($email->getSentDate() !== null) ? $email->getSentDate()
                        ->format('d.m.Y H:i') : '-',
                    // XSS Protection
                    'sender'        => htmlspecialchars((string)$email->getSender()->getEmailAddress(), ENT_QUOTES),
                    'recipient'     => htmlspecialchars((string)$email->getRecipient()->getEmailAddress(), ENT_QUOTES),
                    'subject'       => htmlspecialchars((string)$email->getSubject(), ENT_QUOTES),
                    'is_pending'    => $email->isPending()
                ];
            }
            
            $response = [
                'draw'            => (int)$_REQUEST['draw'],
                'recordsTotal'    => $this->emailService->getRecordCount(),
                'recordsFiltered' => $this->emailService->getRecordCount($keyword),
                'data'            => $tableData
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX - POST] Sends and saves the provided email collection.
     *
     * This method expects the $_POST['collection'] array to be present, containing email
     * records to  be send. Check the "EmailParser" class to see the expected JSON
     * format.
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionSend()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            $postCollection = $this->_getPostData('collection');
            
            if (!$postCollection) {
                throw new AjaxException('Post collection was not set as an argument for "send" method.');
            }
            
            $collection = $this->emailParser->parseCollection($postCollection);
            $this->emailService->sendCollection($collection);
            
            $response = [
                'success' => true,
                'action'  => 'Send',
                'emails'  => $postCollection
            ];
        } catch (AttachmentNotFoundException $ex) {
            // Translate the error for the frontend dialog message.
            $lang                = MainFactory::create_object('LanguageTextManager',
                                                              ['emails', $_SESSION['languages_id']]);
            $translatedException = new Exception($lang->get_text('message_attachment_could_not_be_found') . ' '
                                                 . $ex->getAttachmentPath());
            $response            = AjaxException::response($translatedException);
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX - POST] Queue email records into the database.
     *
     * The queue operation will save the email with a pending status. Queue operation will be executed
     * for all the email records inside the $_POST['collection'] variable.
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionQueue()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            $postCollection = $this->_getPostData('collection');
            
            if (!$postCollection) {
                throw new AjaxException('Post collection was not set as an argument for "queue" method.');
            }
            
            $collection = $this->emailParser->parseCollection($postCollection);
            $this->emailService->queueCollection($collection);
            
            $response = [
                'success' => true,
                'action'  => 'Queue',
                'emails'  => $postCollection
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX - POST] Remove email records from the database.
     *
     * Will remove all the email records inside the $_POST['collection'] variable.
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionDelete()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            $postCollection = $this->_getPostData('collection');
            
            if (!$postCollection) {
                throw new AjaxException('Post collection was not set as an argument for "delete" method.');
            }
            
            $collection = $this->emailParser->parseCollection($postCollection);
            $this->emailService->deleteCollection($collection);
            
            $response = [
                'success' => true,
                'action'  => 'Delete',
                'emails'  => $postCollection
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX - POST] Get email record by ID.
     *
     * This method uses the provided $_POST['email_id'] value to fetch the data of the email and
     * return it to the client. It is not used by the admin/emails page but might be useful in other
     * pages.
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionGet()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            $postEmailId = $this->_getPostData('email_id');
            
            if (!isset($postEmailId)) {
                throw new AjaxException('Email ID was not set as an argument for "get" method.');
            }
            
            $email    = $this->emailService->getById(new IdType($postEmailId));
            $response = $this->emailParser->encodeEmail($email);
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX - POST] Upload new attachment file to server.
     *
     * The $_FILES array contains information about the file that was uploaded. When an email
     * file is uploaded it is stored in the "uploads/tmp" directory until the email is created
     * and then is is moved to its own directory. The reason for this algorithm is that we do
     * not want email attachments to be in one place altogether.
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionUploadAttachment()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            if (!isset($_FILES) || empty($_FILES)) {
                throw new AjaxException('No files where provided for upload.');
            }
            
            // Get the first item of $_FILES array.
            $file               = array_shift($_FILES);
            $tmpAttachmentPath  = MainFactory::create('AttachmentPath', $file['tmp_name']);
            $tmpAttachmentName  = MainFactory::create('AttachmentName', $file['name']);
            $tmpEmailAttachment = MainFactory::create('EmailAttachment', $tmpAttachmentPath, $tmpAttachmentName);
            $newEmailAttachment = $this->attachmentsHandler->uploadAttachment($tmpEmailAttachment);
            
            // Return success response to client.
            $response = [
                'success' => true,
                'action'  => 'UploadAttachment',
                'path'    => (string)$newEmailAttachment->getPath()
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * The $_POST array contains information about the file that was uploaded. When an email
     * file is uploaded it is stored in the "uploads/tmp" directory until the email is created
     * and then is is moved to its own directory. The reason for this algorithm is that we do
     * not want email attachments to be in one place altogether.
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionUploadAttachmentWithFileManager()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            // Return success response to client.
            $response = [
                'success' => true,
                'action'  => 'UploadAttachment',
                'path'    => (string)DIR_FS_CATALOG . 'uploads/tmp/' . $this->_getPostData('attachmentPath')
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Download email attachment file.
     *
     * This method will provide the required headers for downloading the requested attachment file.
     *
     * If the requested file is was not found then an error message will be displayed.
     *
     * @return HttpControllerResponse|AdminLayoutHttpControllerResponse
     */
    public function actionDownloadAttachment()
    {
        // Check provided argument.
        if (!isset($_GET['path']) || empty($_GET['path'])) {
            throw new InvalidArgumentException('$_GET["path"] argument was not provided.');
        }
        
        // Validate argument, the user is only able to download files from the uploads
        // directory. Otherwise there would be a security issue.
        $path                   = realpath($_GET['path']);
        $validateAttachmentsDir = basename(dirname(dirname(dirname($path))));
        $validateTmpDir         = basename(dirname(dirname($path)));
        
        if (file_exists($path) && is_file($path)
            && ($validateAttachmentsDir == 'uploads' || $validateTmpDir == 'uploads')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            
            $basename = basename($path);
            $filename = (strpos($basename, 'email_id') !== false) ? substr($basename,
                                                                           strpos($basename, '-') + 1) : $basename;
            
            header('Cache-Control: public');
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Type: ' . $finfo->file($path));
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            
            return MainFactory::create('HttpControllerResponse', '');
        } else {
            $langMessages = MainFactory::create_object('LanguageTextManager', ['messages', $_SESSION['languages_id']]);
            $title        = $langMessages->get_text('error');
            $template     = 'emails/download_attachment_error.html';
            $data         = ['path' => htmlspecialchars($_GET['path'])];
            
            return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($title, $template, $data);
        }
    }
    
    
    /**
     * [AJAX - POST] Remove existing "tmp" attachment from the server.
     *
     * This method requires an array present in the $postDataArray that contain the paths
     * to the server "tmp" attachments to be removed. It will not remove attachments that reside
     * in other directories because they might be used by other email records (e.g. forward existing
     * email with attachments -> user might remove attachment that is used by the original email
     * record causing problems to the system).
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionDeleteAttachment()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            $postAttachments = $this->_getPostData('attachments');
            
            if (!isset($postAttachments)) {
                throw new AjaxException('No attachments where provided for removal.');
            }
            
            foreach ($postAttachments as $path) {
                if (basename(dirname($path)) === 'tmp') {
                    $attachmentPath  = MainFactory::create('AttachmentPath', $path);
                    $emailAttachment = MainFactory::create('EmailAttachment', $attachmentPath);
                    $this->attachmentsHandler->deleteAttachment($emailAttachment);
                }
            }
            
            $response = [
                'success'     => true,
                'action'      => 'DeleteAttachment',
                'attachments' => $postAttachments
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX - POST] Delete old attachment files from emails.
     *
     * This method will filter the emails and remove their attachments on the provided date
     * and before. This is necessary because the admin user needs a way to clean the old unnecessary
     * files from the server and free some extra space. As an extra action this method will empty the
     * "uploads/tmp" directory.
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionDeleteOldAttachments()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            if ($this->_getPostData('removalDate') === null) {
                throw new Exception('Removal date was not provided with the request.');
            }
            
            // Add one day to the selected removal date so that it will include all the attachments
            // which have a creation time newer than 00:00:00 AM.
            $removalDate = new DateTime();
            $removalDate->setTimestamp(strtotime('+1 day', strtotime($this->_getPostData('removalDate'))));
            
            // Remove old attachments.
            $removalInfo = $this->attachmentsHandler->deleteOldAttachments($removalDate);
            
            // Remove tmp files (if any).
            $this->attachmentsHandler->emptyTempDirectory();
            
            $response = [
                'success' => true,
                'action'  => 'DeleteOldAttachments',
                'count'   => $removalInfo['count'],
                'size'    => [
                    'bytes'     => $removalInfo['size'],
                    'megabytes' => round($removalInfo['size'] / 1024 / 1024, 2) // convert in megabytes
                ]
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX - POST] Delete old emails.
     *
     * This method will delete emails on the provided date and before.
     *
     * @return JsonHttpControllerResponse Returns a success response or exception information.
     */
    public function actionDeleteOldEmails()
    {
        try {
            $this->_validatePageToken(); // CSRF Protection
            
            if ($this->_getPostData('removalDate') === null) {
                throw new Exception('Removal date was not provided with the request.');
            }
            
            // Add one day to the selected removal date so that it will include all the attachments
            // which have a creation time newer than 00:00:00 AM.
            $removalDate = new DateTime();
            $removalDate->setTimestamp(strtotime('+1 day', strtotime($this->_getPostData('removalDate'))));
            
            // Remove old emails
            $this->emailService->deleteOldEmailsByDate($removalDate);
            $this->attachmentsHandler->deleteOldAttachments($removalDate);
            
            $response = [
                'success' => true,
                'action'  => 'DeleteOldEmails'
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX] Get attachments size in MB
     *
     * @return JsonHttpControllerResponse
     */
    public function actionGetAttachmentsSize()
    {
        try {
            $attachmentsSize = $this->attachmentsHandler->getAttachmentsSize(); // in bytes
            
            $response = [
                'success' => true,
                'action'  => 'GetAttachmentsSize',
                'size'    => [
                    'bytes'     => $attachmentsSize,
                    'megabytes' => round($attachmentsSize / 1024 / 1024, 2) // convert to megabytes
                ]
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * [AJAX - GET] Get shops email settings and configure the client accordingly.
     *
     * @return JsonHttpControllerResponse
     */
    public function actionGetEmailSettings()
    {
        try {
            $response = [
                'signature'    => (EMAIL_SIGNATURE !== '') ? EMAIL_SIGNATURE : null,
                'useHtml'      => (EMAIL_USE_HTML == 'true'),
                'replyAddress' => (CONTACT_US_REPLY_ADDRESS !== '') ? CONTACT_US_REPLY_ADDRESS : null,
                'replyName'    => (CONTACT_US_REPLY_ADDRESS !== '') ? CONTACT_US_REPLY_ADDRESS_NAME : null
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Get the table order clause in string.
     *
     * Since the EmailsController handles the page main table it needs to take care of many operations such
     * as filtering, limiting and ordering. This method will return the correct order string for each table,
     * but needs to be updated if there is a change in the column order.
     *
     * @param array $rule Contains the DataTables order data.
     *
     * @return string Returns the order by value to be used by the CI query builder.
     *
     * @link http://www.datatables.net/manual/server-side
     */
    protected function _getTableOrder($rule)
    {
        if (is_array($rule) == false) {
            // parameter protection
            throw new UnexpectedValueException('Invalid parameter format');
        }
        switch ($rule['column']) {
            case 0:
            case 1:
            case 8: // Empty
                $order = [];
                break;
            
            case 2: // Creation Date
                $order = [
                    'emails.creation_date' => $rule['dir']
                ];
                break;
            
            case 3: // Sent Date
                $order = [
                    'emails.sent_date' => $rule['dir']
                ];
                break;
            
            case 4: // Sender
                $order = [
                    'email_contacts.email_address' => $rule['dir'],
                    'email_contacts.contact_type'  => 'desc'
                ];
                break;
            
            case 5: // Recipient
                $order = [
                    'email_contacts.email_address' => $rule['dir'],
                    'email_contacts.contact_type'  => 'asc'
                ];
                break;
            
            case 6: // Subject
                $order = [
                    'emails.subject' => $rule['dir'],
                ];
                break;
            
            case 7: // Status
                $order = [
                    'emails.is_pending' => $rule['dir']
                ];
                break;
            
            default:
                throw new UnexpectedValueException('Provided column index is not present in the table.');
        }
        
        return $order;
    }
}