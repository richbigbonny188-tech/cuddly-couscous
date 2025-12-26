<?php
/* --------------------------------------------------------------
	GeschaeftskundenversandSoapAdapter.inc.php 2020-11-16
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2017 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

/**
 * Class GeschaeftskundenversandSoapAdapter
 *
 * @category   System
 * @package    Extensions
 * @subpackage Geschaeftskundenversand
 */
class GeschaeftskundenversandSoapAdapter
{
    /**
     * @var GeschaeftskundenversandConfigurationStorage
     */
    protected $configuration;
    
    protected $cig_user;
    protected $cig_password;
    protected $endpointUrl;
    
    const WSDL_TIMEOUT = 15;
    
    
    /**
     * GeschaeftskundenversandSoapAdapter constructor.
     *
     * @param GeschaeftskundenversandConfigurationStorage $configuration
     *
     * @throws GeschaeftskundenversandSoapAdapterCigCredentialsMissingException
     */
    public function __construct(GeschaeftskundenversandConfigurationStorage $configuration)
    {
        $this->configuration = $configuration;
        $mode                = $this->configuration->get('mode');
        $this->cig_user      = $this->configuration->get('cig/' . $mode . '/user');
        $this->cig_password  = $this->configuration->get('cig/' . $mode . '/password');
        if (empty($this->cig_user) || empty($this->cig_password)) {
            throw new GeschaeftskundenversandSoapAdapterCigCredentialsMissingException();
        }
        $this->endpointUrl = $this->configuration->get('endpoint/' . $mode);
    }
    
    
    public function getSoapClient()
    {
        $wsdlUrl    = $this->configuration->get('wsdl_url');
        $soapClient = null;
        $options    = [
            'location'       => $this->endpointUrl,
            'authentication' => SOAP_AUTHENTICATION_BASIC,
            'login'          => $this->cig_user,
            'password'       => $this->cig_password,
            'HTTP_PASS'      => $this->cig_password,
            'encoding'       => 'UTF-8',
            'trace'          => 1,
            'soap_version'   => SOAP_1_1,
            'cache_wsdl'     => WSDL_CACHE_BOTH,
        ];
        $this->pingWsdl($wsdlUrl);
        $soapClient       = new SoapClient($wsdlUrl, $options);
        $authdata         = [];
        $authdata[]       = new SoapVar($this->configuration->get('credentials/user'),
                                        XSD_STRING,
                                        null,
                                        'http://dhl.de/webservice/cisbase',
                                        'user');
        $authdata[]       = new SoapVar($this->configuration->get('credentials/password'),
                                        XSD_STRING,
                                        null,
                                        'http://dhl.de/webservice/cisbase',
                                        'signature');
        $authentification = new SoapVar($authdata, SOAP_ENC_OBJECT);
        $headers          = [];
        $headers[]        = new SoapHeader('http://dhl.de/webservice/cisbase', 'Authentification', $authentification);
        $soapClient->__setSoapHeaders($headers);
        
        return $soapClient;
    }
    
    
    public function pingWsdl($wsdlUrl)
    {
        if (strpos($wsdlUrl, 'http') === 1) {
            $curl_options = [
                CURLOPT_URL            => $wsdlUrl,
                CURLOPT_TIMEOUT        => self::WSDL_TIMEOUT,
                CURLOPT_RETURNTRANSFER => true,
            ];
            $ch           = curl_init();
            curl_setopt_array($ch, $curl_options);
            $response  = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            $curlError = curl_error($ch);
            $curlInfo  = curl_getinfo($ch);
            curl_close($ch);
            if ($curlErrno > 0) {
                $exceptionMessage = sprintf('%s (%d)', $curlError, $curlErrno);
                throw new GeschaeftskundenversandSoapAdapterServiceUnavailableException($exceptionMessage);
            }
            if ($curlInfo['http_code'] != 200) {
                $exceptionMessage = sprintf('HTTP %d - %s', $curlInfo['http_code'], $wsdlUrl);
                throw new GeschaeftskundenversandSoapAdapterServiceUnavailableException($exceptionMessage);
            }
        }
    
        if (strpos($wsdlUrl, 'file') === 1) {
            if (!file_exists($wsdlUrl)) {
                throw new GeschaeftskundenversandSoapAdapterServiceUnavailableException('file not found');
            }
        }
    }
    
}
