<?php
ini_set("soap.wsdl_cache_enabled", "0");
/**
 * This code is Jamhuri special and it enables you to access buygoods functionality
 * from any system build on top of php.
 * @author Derrick Rono <derrickrono@gmail.com>
 */

/**
* Class to allow access to lipa na mpesa online checkout
*/
class MpesaApi
{

	public function processCheckOutRequest($password,$MERCHANT_ID,$MERCHANT_TRANSACTION_ID,$REFERENCE_ID,$AMOUNT,$MSISDN,$CALL_BACK_URL){
		$TIMESTAMP=new DateTime();
		$datetime=$TIMESTAMP->format('YmdHis');
		
		$post_string='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="tns:ns">
		<soapenv:Header>
		  <tns:CheckOutHeader>
			<MERCHANT_ID>'.$MERCHANT_ID.'</MERCHANT_ID>
			<PASSWORD>'.$password.'</PASSWORD>
			<TIMESTAMP>'.$datetime.'</TIMESTAMP>
		  </tns:CheckOutHeader>
		</soapenv:Header>
		<soapenv:Body>
		  <tns:processCheckOutRequest>
			<MERCHANT_TRANSACTION_ID>'.$MERCHANT_TRANSACTION_ID.'</MERCHANT_TRANSACTION_ID>
			<REFERENCE_ID>'.$REFERENCE_ID.'</REFERENCE_ID>
			<AMOUNT>'.$AMOUNT.'</AMOUNT>
			<MSISDN>'.$MSISDN.'</MSISDN>
			<!--Optional parameters-->
			<CALL_BACK_URL>'.$CALL_BACK_URL.'</CALL_BACK_URL>
			<CALL_BACK_METHOD>xml</CALL_BACK_METHOD>
			<TIMESTAMP>'.$datetime.'</TIMESTAMP>
		  </tns:processCheckOutRequest>
		</soapenv:Body>
		</soapenv:Envelope>';
		/*
		Headers
		 */
		$headers = array(  
		"Content-type: text/xml",
		"Content-length: ".strlen($post_string),
		"Content-transfer-encoding: text",
		"SOAPAction: \"processCheckOutRequest\"",
		);
		/*
		To get the feedback from the process request system
		For debug purposes only
		 */
		$response=$this->submitRequest(URL,$post_string,$headers);
		echo $response;
		/*
		To get the feedback from the process transaction system
		For debug purposes only
		 */
		echo "Confirm transaction feedback: ".$this->confirmTransaction($response,$datetime,$password,$MERCHANT_ID);

	}
	/*
	The Merchant makes a SOAP call to the SAG to confirm an online checkout transaction
	 */
	public function confirmTransaction($checkoutResponse,$datetime,$password,$MERCHANT_ID){		
		$xml = simplexml_load_string($checkoutResponse);
		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['SOAP-ENV']);
		$sbody = $soap->Body;
		$mpesa_response = $sbody->children($ns['ns1']);
		$rstatus = $mpesa_response->processCheckOutResponse;
		$status = $rstatus->children();		
		$s_returncode = $status->RETURN_CODE;
		$s_description = $status->DESCRIPTION;
		$s_transactionid = $status->TRX_ID;
		$s_enryptionparams = $status->ENC_PARAMS;
		$s_customer_message = $status->CUST_MSG;
		if($s_returncode==42){

			return json_encode("Authentication Failed",401);
		}
		$confirmTransactionResponse='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="tns:ns">
		   <soapenv:Header>
		      <tns:CheckOutHeader>
		         <MERCHANT_ID>'.$MERCHANT_ID.'</MERCHANT_ID>
			<PASSWORD>'.$password.'</PASSWORD>
			<TIMESTAMP>'.$datetime.'</TIMESTAMP>
		      </tns:CheckOutHeader>
		   </soapenv:Header>
		   <soapenv:Body>
		      <tns:transactionConfirmRequest>
		         <!--Optional:-->
		         <TRX_ID>'.$s_transactionid.'</TRX_ID>
		         <!--Optional:-->
		         
		      </tns:transactionConfirmRequest>
		   </soapenv:Body>
		</soapenv:Envelope>';

		$headers = array(  
		"Content-type: text/xml",
		"Content-length: ".strlen($confirmTransactionResponse),
		"Content-transfer-encoding: text",
		"SOAPAction: \"transactionConfirmRequest\"",
		);

		return $this->submitRequest(URL,$confirmTransactionResponse,$headers);
		
	}

	function registerUrl(){

	$registerURLRequest='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:req="http://api-v1.gen.mm.vodafone.com/mminterface/request"> <soapenv:Header>
					      <tns:RequestSOAPHeader xmlns:tns="http://www.huawei.com/schema/osg/common/v2_1">
					         <tns:spId>151515</tns:spId>
					         <tns:spPassword>ODExNERCMDlCNjVDRkYxQUFCNzE5MkE1OEQyMjJDMzk1RUFCNzgwMjk2ODE4Rjk1OTE2MEFGNDU1QkRCMDkyMg==</tns:spPassword>
					         <tns:timeStamp>20140730093620</tns:timeStamp>
					         <tns:serviceId>151515001</tns:serviceId>
				      </tns:RequestSOAPHeader>
			   </soapenv:Header>
			   <soapenv:Body>
			      <req:RequestMsg><![CDATA[<?xml version="1.0" encoding="UTF-8"?>
			<request xmlns="http://api-v1.gen.mm.vodafone.com/mminterface/request">
				    <Transaction>
					        <CommandID>RegisterURL</CommandID>
					        <OriginatorConversationID>Reg-266-1126</OriginatorConversationID>
					        <Parameters>
						            <Parameter>
							                <Key>ResponseType</Key>
							                <Value>Completed</Value>
						            </Parameter>
					        </Parameters>
					        <ReferenceData>
						           <ReferenceItem>
							                <Key>ValidationURL</Key>
							                <Value>http://10.66.49.201:8099/mock</Value>
						            </ReferenceItem>
						<ReferenceItem>
							                <Key>ConfirmationURL</Key>
							                <Value>http://10.66.49.201:8099/mock</Value>
						            </ReferenceItem>
			           
					        </ReferenceData>
				    </Transaction>
				    <Identity>
					        <Caller>
						            <CallerType>0</CallerType>
						            <ThirdPartyID/>
						            <Password/>
						            <CheckSum/>
						<ResultURL/>
					        </Caller>
					        <Initiator>
						            <IdentifierType>1</IdentifierType>
						            <Identifier/>
						            <SecurityCredential/>
						            <ShortCode/>
					        </Initiator>
					        <PrimaryParty>
						            <IdentifierType>1</IdentifierType>
						            <Identifier/>
						            <ShortCode>898942</ShortCode>
					        </PrimaryParty>
				    </Identity>
				    <KeyOwner>1</KeyOwner>
			</request>]]></req:RequestMsg>
			   </soapenv:Body>
			</soapenv:Envelope>';
		$headers = array(  
		"Content-type: text/xml",
		"Content-length: ".strlen($registerURLRequest),
		"Content-transfer-encoding: text",
		"SOAPAction: \"GenericAPIRequest\"",
		);

		echo $this->submitRequest(URL,$registerURLRequest,$headers);
	}
	function submitRequest($url,$post_string,$headers){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST,TRUE); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,  $post_string); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$data = curl_exec($ch);
		if($data === FALSE)
		{
			$err = 'Curl error: ' . curl_error($ch);
			curl_close($ch);
			echo "Error \n".$err;
		}
		else
		{
			curl_close($ch);
			$body = $data;
			
		}
		return $body;
}

}
?>
