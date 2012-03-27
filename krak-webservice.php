<?

/*
 * Krak Webservices - A PHP class querying the paid services from Krak
 * <http://www.krakwebservices.dk/>.
 *
 * Krak is a trademark of Eniro. This code is not provided by or has
 * any relation to Krak, except for the use of their service.
 *
 * Copyright 2012 Kristeligt Dagblad, Mikkel Munch Mortensen
 * <munch@k.dk>.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


/*
 * A class for accessing the Krak Webservices, e.g. for looking up
 * names and addresses by a phone number. Based on the (faulty) code
 * example available from:
 * <http://www.krakwebservices.dk/documentation.html>.
 */
class KrakWebservice
{
	/*
	 * Initialize by getting a valid a ticket.
	 */
	function __construct($username, $password, $product_id)
	{
		// Ini settings.
		ini_set('soap.wsdl_cache_enabled', 1);
		ini_set('soap.wsdl_cache_dir', '/tmp');

		// Setup various internal stuff.
		$this->username = $username;
		$this->password = $password;
		$this->product_id = $product_id;
		$this->locale = 'da-DK';
		$this->namespace = 'http://webservice.krak.dk/';

		// Get a valid ticket.
		$this->ticket = $this->get_ticket();
	}

	/*
	 * Asks the Krak Webservice for a ticket to use later on.
	 */
	function get_ticket()
	{
		// Prepare ticket params.
		$params = array(
			'userName' => $this->username,
			'password' => $this->password,
			'locale'   => $this->locale,
		);

		// Request a ticket.
		$client = new SoapClient('http://login.webservice.krak.dk/ticketcentral.asmx?op=GetTicketByUser&wsdl');
		$results = $client->GetTicketByUser($params);

		// Handle result.
		foreach ($results as $res) {
			$ticket['ticket'] = $res->ticket;
			$ticket['timeout'] = $res->timeout;
			break;
		}

		// Set timeout according to response.
		ini_set('soap.wsdl_cache_ttl', $ticket['timeout']);
		
		return $ticket;
	}

	/*
	 * Get name and address info by looking up a phone number.
	 */
	function get_tele_by_tn($number)
	{
		// Prepare request headers.
		$ticket = array( 'ticket' => $this->ticket['ticket'], 'product' => $this->product_id, 'username' => $this->username);

		// Set Headers
		$SoapHeader[] = new SoapHeader($this->namespace, 'KrakSoapHeader', array('ticket' => $ticket['ticket']));
		$SoapHeader[] = new SoapHeader($this->namespace, 'KrakSoapHeader', array('product' => $this->product_id));
		$SoapHeader[] = new SoapHeader($this->namespace, 'KrakSoapHeader', array('username' => $this->username));

		// Create client and set headers.
		$rclient = new SoapClient('http://basicservices.webservice.krak.dk/telesearch.asmx?WSDL');
		$rclient->__setSoapHeaders($SoapHeader);

		// Prepare request params.
		$params = array(
			'telephoneNumber' => $number
		);

		// Request data.
		$objdata = $rclient->GetTeleByTn($params);

		// Extract resulting data.
		foreach ($objdata as $data)
		{
			if (property_exists($data, 'Tele'))
			{
				return $data->Tele;
			}
		}
		// This one is for empty results.
		return null;
	}

	/*
	 * The rest of the web service calls may be implemented (when needed) some day.
	 * Please get in touch if you do (some of) this.
	 */
}

?>
