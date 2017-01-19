var hpciStatus = "";
var hpciNoConflict = "";

hpciUrlParam = function(name, queryStr){
	var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(queryStr);
	if (!results) { return 0; }
	return results[1] || 0;
};

var sendHPCIMsg = function() { };
sendHPCIMsg = function() {
	// setup non Conflicting handler
	if (hpciNoConflict != "N") {
		jQuery.noConflict();
	}
	console.log('before receiveMessage');
	// setup receive message handler
	jQuery.receiveMessage(
			  function(e){
                 console.log('inside receive message argument functions');
			     hpciStatus = hpciUrlParam('hpciStatus', "?" + e.data);
			     if (hpciStatus == "success") {
			         hpciMappedCCValue = hpciUrlParam('hpciCC', "?" + e.data);
			         hpciMappedCVVValue = hpciUrlParam('hpciCVV', "?" + e.data);
			         hpciCCBINValue = hpciUrlParam('hpciCCBIN', "?" + e.data);
			         hpci3DSecValue = hpciUrlParam('hpci3DSec', "?" + e.data);
			         if (hpci3DSecValue == "verify3dsec") {
				         if (typeof hpciSiteShow3DSecHandler!="undefined") {
				        	 hpciSiteShow3DSecHandler();
				         }
				         else {
				        	 hpciDefaultSiteShow3DSecHandler();
				         }
			         }
			         else if (hpci3DSecValue == "report3dsec") {
			        	 hpci3DSecAuthStatus = hpciUrlParam('hpci3DSecAuthStatus', "?" + e.data);
			        	 hpci3DSecAuthCAVV = hpciUrlParam('hpci3DSecAuthCAVV', "?" + e.data);
			        	 hpci3DSecAuthECI = hpciUrlParam('hpci3DSecAuthECI', "?" + e.data);
			        	 hpci3DSecTxnId = hpciUrlParam('hpci3DSecTxnId', "?" + e.data);
				         if (typeof hpci3DSiteSuccessHandlerV2!="undefined") {
				        	 hpci3DSiteSuccessHandlerV2(hpciMappedCCValue, hpciMappedCVVValue, hpciCCBINValue, hpci3DSecAuthStatus, hpci3DSecAuthCAVV, hpci3DSecAuthECI, hpci3DSecTxnId);
				         }
				         else if (typeof hpci3DSiteSuccessHandler!="undefined") {
				        	 hpci3DSiteSuccessHandler(hpciMappedCCValue, hpciMappedCVVValue, hpci3DSecAuthStatus, hpci3DSecAuthCAVV, hpci3DSecAuthECI, hpci3DSecTxnId);
				         }
				         else {
				        	 hpci3DDefaultSiteSuccessHandler(hpciMappedCCValue, hpciMappedCVVValue, hpci3DSecAuthStatus, hpci3DSecAuthCAVV, hpci3DSecAuthECI, hpci3DSecTxnId);
				         }
			         }
			         else if (hpci3DSecValue == "reportpinverfy") {
			        	 alert("Got hpci3DSecValue:reportpinverfy");
			         }
			         else {
			        	 if (typeof hpciSiteSuccessHandlerV2!="undefined") {
			        		 hpciSiteSuccessHandlerV2(hpciMappedCCValue, hpciMappedCVVValue, hpciCCBINValue);
				         }
			        	 else if (typeof hpciSiteSuccessHandler!="undefined") {
					         hpciSiteSuccessHandler(hpciMappedCCValue, hpciMappedCVVValue);
				         }
				         else {
					         hpciDefaultSiteSuccessHandler(hpciMappedCCValue, hpciMappedCVVValue);
				         }
			         }
			     }
			     else {
			         hpciErrCode = hpciUrlParam('hpciErrCode', "?" + e.data);
			         hpciErrMsgEncoded = hpciUrlParam('hpciErrMsg', "?" + e.data);
			         hpciErrMsg = unescape(hpciErrMsgEncoded);
			         if (typeof hpciSiteErrorHandler!="undefined") {
				    	 hpciSiteErrorHandler(hpciErrCode, hpciErrMsg);
			         }
			         else {
				    	 hpciDefaultSiteErrorHandler(hpciErrCode, hpciErrMsg);
			         }
			     }
			  },
			  hpciCCFrameHost
			);
	console.log('after jQuery.receivemessage');
	// find the uri
	var url = "" + window.location;
	
	// prepare full message to send/post
	var fullMsg = "";
	var msgConcat = "";
	
	// define the parameters for 3D Sec
	var defThreeDSecEnabled = false;
	if (typeof hpciThreeDSecEnabled!="undefined" && hpciThreeDSecEnabled) {
		defThreeDSecEnabled = true;
    }
	
	if (defThreeDSecEnabled) {
		// lookup the parameter names for 3D Sec
		var defExpMonthName = "expMonth";
		var defExpMonthValue = "";
		if (typeof hpciExpMonthName!="undefined" && hpciExpMonthName!="") {
			defExpMonthName = hpciExpMonthName;
	    }
		// find the parameter value
		var expMonthInput = document.getElementById(defExpMonthName);
		if (typeof expMonthInput!="undefined") {
			defExpMonthValue = expMonthInput.value;
	    }
		if (defExpMonthValue!="") {
			fullMsg = fullMsg + msgConcat + "expMonth=" + defExpMonthValue;
			msgConcat = "&";
		}
		
		// lookup year
		var defExpYearName = "expYear";
		var defExpYearValue = "";
		if (typeof hpciExpYearName!="undefined" && hpciExpYearName!="") {
			defExpYearName = hpciExpYearName;
	    }
		// find the parameter value
		var expYearInput = document.getElementById(defExpYearName);
		if (typeof expYearInput!="undefined") {
			defExpYearValue = expYearInput.value;
	    }
		if (defExpYearValue!="") {
			fullMsg = fullMsg + msgConcat + "expYear=" + defExpYearValue;
			msgConcat = "&";
		}
		
		// lookup message id
		var defMessageIdName = "messageId";
		var defMessageIdValue = "";
		if (typeof hpciMessageIdName!="undefined" && hpciMessageIdName!="") {
			defMessageIdName = hpciMessageIdName;
	    }
		// find the parameter value
		var messageIdInput = document.getElementById(defMessageIdName);
		if (typeof messageIdInput!="undefined") {
			defMessageIdValue = messageIdInput.value;
	    }
		if (defMessageIdValue!="") {
			fullMsg = fullMsg + msgConcat + "messageId=" + defMessageIdValue;
			msgConcat = "&";
		}
		
		// lookup transaction id
		var defTransactionIdName = "transactionId";
		var defTransactionIdValue = "";
		if (typeof hpciTransactionIdName!="undefined" && hpciTransactionIdName!="") {
			defTransactionIdName = hpciTransactionIdName;
	    }
		// find the parameter value
		var transactionIdInput = document.getElementById(defTransactionIdName);
		if (typeof transactionIdInput!="undefined") {
			defTransactionIdValue = transactionIdInput.value;
	    }
		if (defTransactionIdValue!="") {
			fullMsg = fullMsg + msgConcat + "transactionId=" + defTransactionIdValue;
			msgConcat = "&";
		}
		
		// lookup display ready transaction amount
		var defTranDispAmountName = "tranDispAmount";
		var defTranDispAmountValue = "";
		if (typeof hpciTranDispAmountName!="undefined" && hpciTranDispAmountName!="") {
			defTranDispAmountName = hpciTranDispAmountName;
	    }
		// find the parameter value
		var tranDispAmountInput = document.getElementById(defTranDispAmountName);
		if (typeof transactionIdInput!="undefined") {
			defTranDispAmountValue = tranDispAmountInput.value;
	    }
		if (defTranDispAmountValue!="") {
			fullMsg = fullMsg + msgConcat + "tranDispAmount=" + defTranDispAmountValue;
			msgConcat = "&";
		}
		
	}
	console.log('// snedHPCMsg: before prepare full message to send/post ');
	// prepare full message to send/post
	fullMsg = fullMsg + msgConcat + "mapcc-url=" + url;
    console.log('fullMsg');
    console.log(fullMsg );
    console.log('hpciStatus');
    console.log(hpciStatus);
    console.log('hpciCCFrameFullUrl');
    console.log(hpciCCFrameFullUrl);
    console.log('frames[hpciCCFrameName]');
    console.log(frames[hpciCCFrameName]);
    try{
        if (hpciStatus != "success") {
            jQuery.postMessage(
              fullMsg,
              hpciCCFrameFullUrl,
              frames[hpciCCFrameName]
            );
            console.log('before return false');

            return false;
        }
        else {
            console.log('before return true');

            return true;
        }
    }
    catch(exception){
        console.log('exception:');
        console.log(exception);
    }
};

var hpci3DDefaultSitePINSuccessHandler = function (){
	alert("Please implement hpci3DSitePINSuccessHandler function to submit form");
}

var hpci3DDefaultSitePINErrorHandler = function (){
	alert("Please implement hpci3DSitePINErrorHandler function to submit form");
}

var receivePINEnabled = "";
var receivePINMsg = function(){ };
receivePINMsg = function() {
	if (receivePINEnabled == "Y")
		return;
	// make sure another listner is not enabled
	receivePINEnabled = "Y";
	
	// setup receive message handler
	jQuery.receiveMessage(
			  function(e){
			     hpciStatus = hpciUrlParam('hpciStatus', "?" + e.data);
			     if (hpciStatus == "success") {
			         hpci3DSecValue = hpciUrlParam('hpci3DSec', "?" + e.data);
			         if (hpci3DSecValue == "reportpinverify") {
			        	 // alert("Got hpci3DSecValue:reportpinverify");
				         if (typeof hpci3DSitePINSuccessHandler!="undefined") {
				        	 hpci3DSitePINSuccessHandler();
				         }
				         else {
				        	 hpci3DDefaultSitePINSuccessHandler();
				         }
			         }
			         else {
				         if (typeof hpci3DSitePINErrorHandler!="undefined") {
				        	 hpci3DSitePINErrorHandler();
				         }
				         else {
				        	 hpci3DDefaultSitePINErrorHandler();
				         }
			         }
			     }
			     else {
			         if (typeof hpci3DSitePINErrorHandler!="undefined") {
			        	 hpci3DSitePINErrorHandler();
			         }
			         else {
			        	 hpci3DDefaultSitePINErrorHandler();
			         }
			     }
			  },
			  hpciCCFrameHost
			);
};

var sendHPCIChangeStyleMsg = function() { };
sendHPCIChangeStyleMsg = function(elementId, propName, propValue) {
	// setup non Conflicting handler
	if (hpciNoConflict != "N") {
		jQuery.noConflict();
	}
	
	// prepare full message to send/post
	var fullMsg = "msgCmd=changestyle&elementId=" + elementId + "&propName=" + encodeURIComponent(propName) + "&propValue=" + encodeURIComponent(propValue);
	jQuery.postMessage(
	  fullMsg,
	  hpciCCFrameFullUrl,
	  frames[hpciCCFrameName]
	);
    return true;
};
