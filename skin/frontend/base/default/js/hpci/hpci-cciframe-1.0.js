var hpciStatus = "";
var hpciNoConflict = "";
var hpciPro = new Object(); //temporary object for consolidated look of hpci state
hpciUrlParam = function(name, queryStr){
	var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(queryStr);
	if (!results) { return 0; }
	return results[1] || 0;
};

var sendHPCIMsg = function() { };
sendHPCIMsg = function() {
	test_log('inside sendHPCIMsg');
	// setup non Conflicting handler
	if (hpciNoConflict != "N") {
		jQuery.noConflict();
	}
	// setup receive message handler (we have got message from HPCI after CC data sending)
	jQuery.receiveMessage(
			  function(e){
                 test_log('inside receive message argument functions');
                  test_log(e);

                  hpciPro.hpciStatus  = hpciStatus = hpciUrlParam('hpciStatus', "?" + e.data);
                  test_log('hpciStatus:'+hpciStatus);


                  if (hpciStatus == "success") {
                     hpciPro.hpciMappedCCValue = hpciMappedCCValue = hpciUrlParam('hpciCC', "?" + e.data);
			         hpciPro.hpciMappedCVVValue  = hpciMappedCVVValue = hpciUrlParam('hpciCVV', "?" + e.data);
			         hpciPro.hpciCCBINValue  = hpciCCBINValue = hpciUrlParam('hpciCCBIN', "?" + e.data);
			         hpciPro.hpci3DSecValue  = hpci3DSecValue = hpciUrlParam('hpci3DSec', "?" + e.data);
                     test_log(hpciPro);
			         if (hpci3DSecValue == "verify3dsec") {
				         if (typeof hpciSiteShow3DSecHandler!="undefined") {
				        	 hpciSiteShow3DSecHandler();
				         }
				         else {
				        	 hpciDefaultSiteShow3DSecHandler();
				         }
			         }
			         else if (hpci3DSecValue == "report3dsec") {
			        	 hpciPro.hpci3DSecAuthStatus  = hpci3DSecAuthStatus = hpciUrlParam('hpci3DSecAuthStatus', "?" + e.data);
			        	 hpciPro.hpci3DSecAuthCAVV  = hpci3DSecAuthCAVV = hpciUrlParam('hpci3DSecAuthCAVV', "?" + e.data);
			        	 hpciPro.hpci3DSecAuthECI  = hpci3DSecAuthECI = hpciUrlParam('hpci3DSecAuthECI', "?" + e.data);
			        	 hpciPro.hpci3DSecTxnId  = hpci3DSecTxnId = hpciUrlParam('hpci3DSecTxnId', "?" + e.data);
                         test_log(hpciPro);

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
                             test_log('we are going to call hpciSiteSuccessHandler('+hpciMappedCCValue+',' + hpciMappedCVVValue+') function')
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
	test_log('after jQuery.receivemessage declaration');
	// find the uri
	var url = "" + window.location;

	// prepare full message to send/post
	var fullMsg = "";
	var msgConcat = "";

	// define the parameters for 3D Sec
	var defThreeDSecEnabled = false;
	if (typeof hpciThreeDSecEnabled!="undefined" && hpciThreeDSecEnabled) {
        hpciPro.defThreeDSecEnabled = defThreeDSecEnabled = true;
    }

	if (defThreeDSecEnabled) {
		// lookup the parameter names for 3D Sec
		var defExpMonthName = "expMonth";
		var defExpMonthValue = "";
		if (typeof hpciExpMonthName!="undefined" && hpciExpMonthName!="") {
            hpciPro.defExpMonthName  = defExpMonthName = hpciExpMonthName;
	    }
		// find the parameter value
		var expMonthInput = document.getElementById(defExpMonthName);
		if (typeof expMonthInput!="undefined") {
            hpciPro.defExpMonthValue = defExpMonthValue = expMonthInput.value;
	    }
		if (defExpMonthValue!="") {
			fullMsg = fullMsg + msgConcat + "expMonth=" + defExpMonthValue;
			msgConcat = "&";
		}

		// lookup year
		var defExpYearName = "expYear";
		var defExpYearValue = "";
		if (typeof hpciExpYearName!="undefined" && hpciExpYearName!="") {
            hpciPro.defExpYearName = defExpYearName = hpciExpYearName;
	    }
		// find the parameter value
		var expYearInput = document.getElementById(defExpYearName);
		if (typeof expYearInput!="undefined") {
            hpciPro.defExpYearValue = defExpYearValue = expYearInput.value;
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
	// prepare full message to send/post
    hpciPro.fullMsg  = fullMsg = fullMsg + msgConcat + "mapcc-url=" + url;
    hpciPro.hpciCCFrameFullUrl  = hpciCCFrameFullUrl;
    hpciPro.hpciCCFrameName  = hpciCCFrameName;
    try{
        test_log('sendHPCIMsg: before error verirification');
        test_log(hpciPro);
        if (1 || hpciStatus != "success") {
            test_log('inside hpciStatus error handling');

            // post <message: mapcc-url=current_url><target url: hpci iframe url><target iframe>
            jQuery.postMessage(
              fullMsg,
              hpciCCFrameFullUrl,
              frames[hpciCCFrameName]
            );
            test_log('after jQuery.postMessage');

            return false;
        }
    }
    catch(exception){
        test_log('exception:');
        test_log(exception);
    }
    test_log('end of sendHPCIMsg');
    return true;
};

var hpci3DDefaultSitePINSuccessHandler = function (){
	alert("Please implement hpci3DSitePINSuccessHandler function to submit form");
}

var hpci3DDefaultSitePINErrorHandler = function (){
	alert("Please implement hpci3DSitePINErrorHandler function to submit form");
}

var receivePINEnabled = "";
var receivePINMsg = function() { };
receivePINMsg = function() {
    test_log('inside receivePINMsg');
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
    test_log('inside sendHPCIChangeStyleMsg');
	// setup non Conflicting handler
	if (hpciNoConflict != "N") {
		jQuery.noConflict();
	}
	test_log('1!!!!!');
	// prepare full message to send/post
	var fullMsg = "msgCmd=changestyle&elementId=" + elementId + "&propName=" + encodeURIComponent(propName) + "&propValue=" + encodeURIComponent(propValue);
	jQuery.postMessage(
	  fullMsg,
	  hpciCCFrameFullUrl,
	  frames[hpciCCFrameName]
	);
    return true;
	
};

function test_log(msg){
    return;
    console.log(msg);
}