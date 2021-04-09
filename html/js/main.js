$(document).ready(function(){
  window.oq=new Oq();
});
var Oq=function(onPost){
  var _self=this;
  this.aSettings={};
  if(typeof(onPost)!='function')onPost=function(data){console.log(data);return data};

  //oq.getSettings(function(){console.log(oq.aSettings)});
  this.getSettings=function(onRefrash){
    $.post('getInfoblock/jssettings/',function(data){
      _self.aSettings=data;
      if(typeof(onRefrash)=='function')onRefrash(data);
      $('body').trigger('oqGetSettings');
      onPost(data);
    },'json');
    return _self;
  }
  this.getSettings();

  //oq.registerUser(1,true,function(oUser){console.log(oUser)});
  this.registerUser=function(_type,_print,onRefrash){
    $.post('registerUser/',{'type':_type,'ajax':'y'},function(data){
      if(data.error=='y')console.log('Не зарегистрирован :(');
      else{
        if(typeof(_print)!='undefined'&&_print!=false)oq.printTalon(data.data);
        if(data.data&&typeof(onRefrash)=='function')onRefrash(data);
      }
    },'json');
    return _self;
  }

  //oq.getUser(2345,function(oUser){console.log(oUser)});
  //oq.getUser(2345,function(oUser){oq.printTalon(oUser)});
  this.getUser=function(id,onRefrash){
    $.post('getUser/',{'id':id,'ajax':'y'},function(data){
      if(data.error=='y')console.log('Не зарегистрирован :(');
      else if(data.data&&typeof(onRefrash)=='function')onRefrash(data.data);
    },'json');
    return _self;
  }

  //oq.getUserList('new',function(oUserList){console.log(oUserList)});
  //Status - new, process, closed
  this.getUserList=function(_status,onRefrash){
    if(typeof(_status)=='undefined')_status='new';
    $.post('getUserList/',{'_status':_status,'ajax':'y'},function(data){
      if(data.error=='y')console.log('userList error :(');
      else if(data.data&&typeof(onRefrash)=='function')onRefrash(data.data);
    },'json');
    return _self;
  }

  //oq.getAllUserList(function(oUserList){console.log(oUserList, htmlDate)});
  this.getAllUserList=function(onRefrash){
    $.post('getAllUserList/',{'ajax':'y'},function(data){
      if(data.error=='y')console.log('allUserList error :(');
      else if(data.data&&typeof(onRefrash)=='function')onRefrash(data.data, data.date);
    },'json');
    return _self;
  }

  //oq.setUserStatus(2,'process',4,function(err){console.log(err)});
  //Status - new, process, closed
  this.setUserStatus=function(userId, _status, _position, onRefrash){
    if(typeof(_position)=='undefined')_position='0';
    $.post('setUserStatus/',{'userId':userId,'_status':_status,'_position':_position,'ajax':'y'},function(error){
      if(error=='y')console.log('setUserStatus error :(');
      else if(typeof(onRefrash)=='function')onRefrash(error);
    });
    return _self;
  }

  //oq.setCurrentPosition(7);
  this.setCurrentPosition=function(n,onRefrash){
    $.post('getInfoblock/jssetposition/',{'oq_current_position':n});
    if(typeof(onRefrash)=='function')onRefrash(n);
  }

  //oq.printTalon(oUser)
  this.printTalon=function(oUser){
    $('#op_printed').show('fast');
    setTimeout(function(){
    	$.post('getInfoblock/talon/',{'oUser':oUser},function(data){_self._printTalon(data)});
    },300);
    return _self;
  }
  this._printTalon=function(_html){
    if(_html==''){
      console.log('Нет талона :(');
      return false;
    }
    if($('#usr_talon').length>0)$('#usr_talon').remove();
    var n_frm=$('<iframe>',{'id':'usr_talon'}).appendTo('body');
    var _printed=$('#op_printed').show('fast');
    window.setTimeout(function(){_printed.css('display','none')},5000);
    n_frm=n_frm[0];
    n_frm.contentWindow.document.open();
    n_frm.contentWindow.document.write(_html);
    n_frm.contentWindow.document.close();
    n_frm=$(n_frm);
    n_frm.css({
      'width':'0',
      'height':'0',
      'position':'absolute',
      'top':'-10px'
    });	
    n_frm.get(0).contentWindow.print();
  }
}