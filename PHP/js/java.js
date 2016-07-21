/* Global javascript functions */

/* ----- jQuery is required ----- */
var jQueryDefined;

if (typeof jQuery === 'undefined') {
  jQueryDefined = false;
  throw new Error('jQuery is required');
}else{
  jQueryDefined = true;
}

/* ----- check validate functions --- */
/* check email address end with swin.edu.au */
function isValidEmail(email){
  email = jQuery.trim(email);
  var reg = /^[a-zA-z0-9_-]+(\.[a-zA-Z0-9_-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.([a-zA-Z]{2,4})$/;
	if(reg.test(email)){
    $es = email.split('@');
    if($es.length>1 && ($es[1]=='swin.edu.au' || $es[1]=='student.swin.edu.au') ){
        return true;
    }
  }
  return false;
}

function isValidDate(dt){
  if(dt==""){ return false; }
  var ts = Date.parse(dt);
	if (!isNaN(ts)){
    return true;
  }
  return false;
}
// DD/MM/YYYY
function isValidDateFormat(dt){
  if(isValidDate(dt)){
    var reg = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
    return dt.match(reg);
  }
  return false;
}
// HH:MM AM/PM
function isValidTimeFormat(t){
  if(t==""){ return false; }
  var reg = /^(\d{1,2}):(\d{1,2})\s?(?:AM|PM)?$/i;
  if(reg.test(t)){
    var ts = t.split(':');
    // check hour
    if(t.indexOf('M')>-1){
      // AM or PM
      if(parseInt(ts[0])<1 || parseInt(ts[0])>12){
        return false;
      }
      var mm = ts[1].substring(0,ts[1].indexOf('M')-1); //59 PM
      if(parseInt(mm)>59){
        return false;
      }
    }else{
      // 24 H
      if(parseInt(ts[0])>23){
        return false;
      }
      if(parseInt(ts[1])>59){
        return false;
      }
    }
    return true;
  }
  return false;
}


/* ----- ajax request response ----- */
var ajaxObj = null;

// abort ajax
function ajaxAbort(){
  if(ajaxObj!=null){
    ajaxObj.abort();
  }
} //.ajaxAbort

// jquery ajax sending a serialized form
// params: url, form, complete, method, log
// call complete function, passing response object {error,data}
function ajaxForm(params){
  var response = {'error':'','data':null};

  // check arguments
  if(!params){
    throw new Error('Function requires arguments');
    return false;
  }
  if(!params.url){
    throw new Error('Url argument is required');
    return false;
  }
  if(!params.form){
    throw new Error('Form argument is required');
    return false;
  }
  try{
    var formData = jQuery(params.form).serialize();
  }catch(e){
    throw new Error('Unable serialize form');
    return false;
  }
  if(!params.complete){
    throw new Error('Complete function is required');
    return false;
  }else if(typeof params.complete !== 'function'){
    throw new Error('Complete must be a function');
    return false;
  }
  if(!params.method){
    params.method = 'GET';
  }
  if (typeof jQuery === 'undefined') {
    response.error = 'jQuery is required';
    params.complete(response);
    return false;
  }
  // log or not
  if(params.log){console.log('Request Data: '+formData);}
  // ajax
  if(ajaxObj!=null){
    ajaxObj.abort();
  }
  ajaxObj = jQuery.ajax({
    'method':params.method,
    'url':params.url,
    'data':formData,
    'success':function(data,status,xhr){
      if(params.log){console.log('Response Data: '+data);}
      try{
        response = JSON.parse(data);
        params.complete(response);
      }catch(e){
        response.error = 'Unexpected error, unknown respone.';
        params.complete(response);
      }
    },
    'error':function(xhr,status,error){
      response.error = error;
      params.complete(response);
    }
  });
}// end ajaxForm


// jquery ajax get data
// params: url, data, complete, method, log
// call complete function, passing response object {error,data}
function ajaxData(params){
  var response = {'error':'','data':null};

  // check arguments
  if(!params){
    throw new Error('Function requires arguments');
    return false;
  }
  if(!params.url){
    throw new Error('Url argument is required');
    return false;
  }
  if(!params.data){
    throw new Error('Data argument is required');
    return false;
  }
  if(!params.complete){
    throw new Error('Complete function is required');
    return false;
  }else if(typeof params.complete !== 'function'){
    throw new Error('Complete must be a function');
    return false;
  }
  if(!params.method){
    params.method = 'GET';
  }
  if (typeof jQuery === 'undefined') {
    response.error = 'jQuery is required';
    params.complete(response);
    return false;
  }
  // ajax
  if(ajaxObj!=null){
    ajaxObj.abort();
  }
  ajaxObj = jQuery.ajax({
    'method':params.method,
    'url':params.url,
    'data':params.data,
    'success':function(data,status,xhr){
      if(params.log){console.log('Response Data: '+data);}
      try{
        response = JSON.parse(data);
        params.complete(response);
      }catch(e){
        response.error = 'Unexpected error, unknown respone.';
        params.complete(response);
      }
    },
    'error':function(xhr,status,error){
      response.error = error;
      params.complete(response);
    }
  });
}
/* ----- end ajax request response ----- */

/* ----- display bootstrap modal for waiting process */
function modalWait(show,title,text,oncancel){
  if(show){
    jQuery('#modal_wait').modal({'show':true,'keyboard':false,'backdrop':'static'});
    if(title){
      jQuery('#modal_wait_title').html(title);
    }
    if(text){
      jQuery('#modal_wait_text').html(text);
    }
    if(typeof(oncancel)==='function'){
      jQuery('#modal_wait_close').show();
      jQuery('#modal_wait_close').off().on('click',function(){
        if(oncancel()!==false){
          jQuery('#modal_wait').modal('hide');
        }
      });
    }else{
      jQuery('#modal_wait_close').hide();
    }
  }else{
    jQuery('#modal_wait').modal('hide');
  }
  return show;
} //.madalWait
/* ----- end display bootstrap modal ------ */

/* ----- overlay block screen ----- */
// display overlay dialog and block
function overlayBlockScreen(block,msg){
  if(!jQueryDefined){ return false; }
  var blockEl = jQuery('#doc_block_overlay');
  if(blockEl.length<=0){
    blockEl = jQuery('body').append('<div id="doc_block_overlay" class="screen-block-overlay"><span><img src="images/loading.gif"> &nbsp;<span id="doc_block_title"></span></span></div>');
  }
  if(block){
    if(msg){
      jQuery('#doc_block_title').html(msg);
    }else{
      jQuery('#doc_block_title').html('Please wait ...');
    }
    blockEl.fadeIn();
  }else{
    blockEl.fadeOut();
  }
  return block;
} //.overlayBlockScreen
/* ----- end overlay block screen ----- */
