/**
 *  Dynarch Horizontal Menu, hmenu-2.9
 *  Copyright Dynarch.com, 2003-2006.  All Rights Reserved.
 *  http://www.dynarch.com/products/dhtml-menu/
 *
 *  THIS NOTICE MUST REMAIN INTACT!
 *
 *           LICENSEE: Dynarch.com user: dedys
 *        License key: linkware-7293688
 *      Purchase date: Mon Dec 11 19:48:52 2006 GMT
 *       License type: linkware
 *
 *  For details on this license please visit
 *  the product homepage at the URL above.
 */
(function()
{
  var UA=navigator.userAgent,w=window;
  _dynarch_top=window.parent;
  try
  {
    _dynarch_top._dynarch_menu_test=null;
  }
  catch(ex)
  {
    _dynarch_top=w;
  }
  w.is_gecko=/gecko/i.test(UA);
  w.is_opera=/opera/i.test(UA);
  w.is_ie=/msie/i.test(UA)&&!is_opera&&!(/mac_powerpc/i.test(UA));
  w.is_ie5=is_ie&&/msie 5\.[^5]/i.test(UA);
  w.is_mac_ie=/msie.*mac/i.test(UA);
  w.is_khtml=/Konqueror|Safari|KHTML/i.test(navigator.userAgent);
  if(typeof _dynarch_top._dynarch_menu_url=="undefined")
    _dynarch_top._dynarch_menu_url="/hmenu/";
  else
  {
    _dynarch_top._dynarch_menu_url=_dynarch_top._dynarch_menu_url.replace(/\x2f*$/,'/');
    if (!/^(https?:|\x2f)/.test(_dynarch_top._dynarch_menu_url)&&_dynarch_top!=window)
      _dynarch_top._dynarch_menu_url=_dynarch_top.document.URL.replace(/\x2f?[^\x2f]*$/,"/")+_dynarch_top._dynarch_menu_url;
  }
  w._dynarch_menu_shadow=new Image();
  w._dynarch_menu_shadow.src=_dynarch_top._dynarch_menu_url+"img/shadow.png";
  w._dynarch_menu_ediv="<div unselectable='on'>&nbsp;</div>";
}
)();

function DynarchMenu(el,config)
{
  var T1,a,i;
  if(config.d_profile)
  {
    DynarchMenu.profile={item:0,tree:0};
    T1=(new Date()).getTime();
  }
  this._baseMenuInfo=null;
  this._popupMenus=[];
  this._activeKeymap=null;
  this._globalKeymap=null;
  this._activePopup=null;
  this._fixed=false;
  this.items={};
  this.target=null;
  this.config=config;
  try
  {
    this._df=config.frames.popups.document.createDocumentFragment();
  }
  catch(e)
  {
    this._df=null;
    this._ca=[];
  }
  el.parentNode.insertBefore(this.createMenuTree(el,!config.vertical),el);
  if(this._df)
  {
    config.container.appendChild(this._df);
    this._df=null;
  }
  else
  {
    a=this._ca;
    for(i=a.length;--i>=0;) config.container.appendChild(a[i]);
  }
  if (config.d_profile) 
    alert("DynarchMenu: generated in "+(((new Date()).getTime()-T1)/1000)+" sec.\n"+"containing "+DynarchMenu.profile.item+" items in "+DynarchMenu.profile.tree+" (sub)menus.");
  if(config.setFocus)
    config.frames.popups.focus();
};

DynarchMenu._hiderID=0;
DynarchMenu._createHider=function(win)
{
  var f=null;
  if(is_ie&&!is_ie5)
  {
    var filter='filter:progid:DXImageTransform.Microsoft.alpha(style=0,opacity=0);';
    var id='dynarch-menu-hider-'+(++this._hiderID);
    win.document.body.insertAdjacentHTML('beforeEnd','<iframe id="'+id+'" scroll="no" frameborder="0" '+'style="position:absolute;visibility:hidden;'+filter+'border:0;top:0;left:0;width:0;height:0;" '+'src="javascript:false;"></iframe>');
    f=win.document.getElementById(id);
  }
  return f;
};

DynarchMenu._setupHider=function(f,x,y,w,h)
{
  if(f)
  {
    var s=f.style;
    s.left=x+"px";
    s.top=y+"px";
    s.width=w+"px";
    s.height=h+"px";
    s.visibility="visible";
  }
};

DynarchMenu._closeHider=function(f)
{
  if(f)f.style.visibility="hidden";
};

DynarchMenu._C=null;
DynarchMenu._T=null;
DynarchMenu._OT=null;
DynarchMenu._RE_PR=/(^|\s+)pressed(\s+|$)/ig;
DynarchMenu._RE_AH=/(^|\s+)active|hover(\s+|$)/ig;
DynarchMenu._RE_DS=/(^|\s+)disabled(\s+|$)/ig;
DynarchMenu._RE_CP=/clones-popup-(.*)/;
DynarchMenu._RE_OPL=/(^|\s+)open-left(\s+|$)/;
DynarchMenu._RE_CTX_ID=/context-for-(.*)/;
DynarchMenu._RE_CTX_CL=/context-class-([^-\s]+)-([^\s]+)/;
DynarchMenu._RE_CTX_AL=/context-align-([a-z]+)/;
DynarchMenu._RE_SCROLL_D=/(^|\s+)dynarch-menu-scroll-(up-|down-)?disabled/g;
DynarchMenu._RE_SCROLL_H=/(^|\s+)dynarch-menu-scroll-(up-|down-)?hover/g;
DynarchMenu._activeItem=null;
DynarchMenu._menus=null;
DynarchMenu._nop=function(){};
DynarchMenu.setup=function(el,args)
{
  if(typeof args=="undefined")args={};
  var config={},tmp;
  function PD(name,value)
  {
    var v=args[name];
    config[name]=(typeof v=="undefined")?value:v;
  };
  PD("className",null);
  PD("tooltips",false);
  PD("shadows",[4,4]);
  PD("smoothShadow",true);
  PD("dx",0);
  PD("dy",0);
  PD("basedx",0);
  PD("basedy",0);
  PD("timeout",150);
  PD("baseTimeout",50);
  PD("context",false);
  PD("vertical",false);
  PD("electric",config.vertical?250:false);
  PD("blink",false);
  PD("lazy",false);
  PD("d_profile",false);
  PD("toolbar",false);
  PD("ctxbutton",2);
  PD("frames",{main:window,popups:window});
  PD("scrolling",null);
  if(config.scrolling===true){config.scrolling={step1:5,step2:10,speed:30};}
  tmp=config.frames;
  if(typeof tmp.main=="string")tmp.main=_dynarch_top.frames[tmp.main];
  if(typeof tmp.popups=="string")tmp.popups=_dynarch_top.frames[tmp.popups];
  PD("crossFrames",tmp.main!==tmp.popups);
  PD("container",config.frames.popups.document.body);
  PD("clone",false);
  PD("onPopup",DynarchMenu._nop);
  PD("setFocus",true);
  if(config.blink===true)config.blink=5;
  if(typeof el=="string")el=tmp.main.document.getElementById(el);
  if(is_mac_ie)return null;
  if(is_ie5)config.smoothShadow=false;
  if(config.context)config.vertical=true;
  if(!el){alert("Error: menu element not found.");
  return false;
}
el.style.display="none";
var i,els,a=DynarchMenu._menus,tmp2;
if(!a||a.length==0){a=DynarchMenu._menus=[];
els=[config.frames.main,config.frames.main.document];
if(config.frames.popups!==config.frames.main){els[els.length]=config.frames.popups;
els[els.length]=config.frames.popups.document;}for(i=els.length;--i>=0;){tmp=els[i];DynarchMenu.watchFrame(tmp,tmp);}DynarchMenu._eventElements=els;}return a[a.length]=new DynarchMenu(el,config);};document.DynarchMenu=DynarchMenu;_dynarch_top.DynarchMenu=DynarchMenu;DynarchMenu.watchFrame=function(f,w){if(typeof w=="undefined")w=window;try{DynarchMenu._addEvent(f,(is_ie||is_opera)?"keydown":"keypress",w.DynarchMenu._documentKeyPress);DynarchMenu._addEvent(f,"mousedown",w.DynarchMenu._documentMouseDown);DynarchMenu._addEvent(f,"mouseup",w.DynarchMenu._documentMouseUp);DynarchMenu._addEvent(f,"mouseover",w.DynarchMenu._documentMouseOver);}catch(e){};};DynarchMenu._clearTimeout=function(){if(_dynarch_top.DynarchMenu._T){_dynarch_top.clearTimeout(_dynarch_top.DynarchMenu._T);_dynarch_top.DynarchMenu._T=null;}};DynarchMenu._forAllMenus=function(callback){for(var i=_dynarch_top.DynarchMenu._menus.length;--i>=0&&!callback(_dynarch_top.DynarchMenu._menus[i]););};DynarchMenu._closeOtherMenus=function(menu){DynarchMenu._forAllMenus(function(tmp){if(tmp!=menu){var a=tmp._popupMenus,i;for(i=a.length;--i>=0;)a[i].close(false,true);tmp._baseMenuInfo.close();window.status="";}});};DynarchMenu.prototype.cloneEl=function(el,doc){if(this.config.clone){if(el.ownerDocument!=doc){if(is_ie){var div=doc.createElement("div");div.innerHTML=el.outerHTML;el=div.removeChild(div.firstChild);}else el=doc.importNode(el,true);}else el=el.cloneNode(true);}return el;};DynarchMenu.prototype.addIcon=function(info,item,icon){var doc=info.parent.base?this.config.frames.main.document:this.config.frames.popups.document;var CE=DynarchMenu._createElement,t,l=item.firstChild.firstChild,r=CE("tr",CE("tbody",t=CE("table",null,doc))),td1=CE("td",r),td2=CE("td",r);icon.unselectable="on";td1.appendChild(r=this.cloneEl(icon,doc));td1.className="icon";td2.className="label";while(l){td1=l.nextSibling;td2.appendChild(l);l=td1;}t.cellSpacing=t.cellPadding=0;t.style.borderCollapse="collapse";item.firstChild.appendChild(t);return r;};DynarchMenu.prototype.createMenuItem=function(li,parent,horiz,arrow){var tmp,ctx=null,cfg=this.config,licl=li.className,icon=null,label,html_popup=true,tooltip,action=null,item,info,self=this,key=null,disabled=/(^|\s+)disabled(\s+|$)/i.test(licl),nohover=DynarchMenu.getCA(li,"nohover"),win=parent.base?cfg.frames.main:cfg.frames.popups,doc=win.document,CE=DynarchMenu._createElement;if(cfg.d_profile)++DynarchMenu.profile.item;tmp=DynarchMenu._getChildrenByTagName(li,"a");tmp=tmp.length>0?tmp[0]:li;label=DynarchMenu._getLabel(tmp);if(typeof label=="string"){label=label.replace(/(^\s+|\s+$)/g,'');if(/^a$/i.test(tmp.tagName)&&tmp.accessKey){key=tmp.accessKey;tmp.accessKey="";}else if(!/^<img/i.test(label)){label=label.replace(/_([a-zA-Z0-9])/,"<u unselectable='on'>$1</u>");key=RegExp.$1;label=label.replace(/__/,"_");}html_popup=false;}tooltip=/^\s*$/.test(tmp.title)?"":tmp.title;if(tmp.href&&/\S/.test(tmp.href)){if(/^javascript:(.*)$/i.test(tmp.href))action=new DynarchMenu.JSAction(RegExp.$1);else action=new DynarchMenu.LinkAction(tmp.href,tmp.target);action.className="explicit-action";action.explicit=true;}else action=new DynarchMenu.DefaultAction(li);tmp=DynarchMenu._getChildrenByTagName(li,"img");if(tmp.length>0)icon=tmp[0];info=new DynarchMenu.MenuItem({html_popup:html_popup,separator:html_popup||!/\S/.test(label)&&!icon,icon:icon,label:label,parent:parent,submenu:null,tooltip:tooltip,action:action,menu:this,disabled:disabled,nohover:nohover,align:licl?(DynarchMenu._RE_CTX_AL.test(licl)?RegExp.$1:"mouse"):"mouse"});if(li.id)this.items[info.id=li.id]=info;if(action)action.info=info;if(horiz){item=CE("td",null,doc);info.labelTD=item;if(info.separator)item.innerHTML="<div unselectable='on'></div>";else{item.innerHTML="<div unselectable='on'>"+label+"</div>";if(icon)info.icon=this.addIcon(info,item,icon);}}else{item=CE("tr",null,doc);tmp=CE("td",item);if(info.separator&&!html_popup){tmp.innerHTML=_dynarch_menu_ediv;tmp.colSpan=3;}else{tmp.className="icon";if(icon)tmp.appendChild(info.icon=this.cloneEl(icon,doc));else tmp.innerHTML=_dynarch_menu_ediv;tmp=CE("td",item);tmp.className="label";info.labelTD=tmp;if(html_popup)tmp.appendChild(this.cloneEl(label,doc));else tmp.innerHTML=label;tmp=CE("td",item);tmp.className="end";tmp.innerHTML=_dynarch_menu_ediv;if(arrow)tmp.className+=" arrow";}}if(is_ie&&key&&parent.base){tmp=CE("a",item);tmp.href="#";tmp.accessKey=key;}info.element=item;item.className=(info.separator&&!html_popup)?"separator":"item";if(action.className)item.className+=" "+action.className;if(disabled)info.disabled=true;if(cfg.tooltips)item.title=info.tooltip;DynarchMenu.addInfo(item,"__msh_info",info);if(DynarchMenu._RE_CTX_ID.test(licl)){ctx=document.getElementById(RegExp.$1);if(ctx)DynarchMenu.setupContext(ctx,info);}else if(DynarchMenu._RE_CTX_CL.test(licl)){ctx=document.getElementsByTagName(RegExp.$1);tmp=new RegExp('(^|\\s)'+RegExp.$2+'(\\s|$)');for(i=ctx.length;--i>=0;)if(tmp.test(ctx[i].className))DynarchMenu.setupContext(ctx[i],info);}else if(licl)item.className+=" "+licl;if(!nohover){if(html_popup)item.onmouseover=win.DynarchMenu.EventHandlers.popup_resetActive;if(key)parent.keymap[key.toLowerCase()]=info;item.onmouseover=win.DynarchMenu.EventHandlers.item_onMouseOver;
    if(!info.separator)
    {
      item.onmouseout=win.DynarchMenu.EventHandlers.item_onMouseOut;
      item.onmousedown=win.DynarchMenu.EventHandlers.item_onMouseDown;
    }
  }
  return item;
};

DynarchMenu._documentMouseDown=function(ev)
{
  ev||(ev=window.event);
  var el=is_ie?ev.srcElement:ev.target,j;
  for(j=el;j&&!j.__msh_info;j=j.parentNode);
  if(!j||j.__msh_info.base)_dynarch_top.DynarchMenu._closeOtherMenus(j&&j.__msh_info.menu);
};
DynarchMenu._msupTimeout=null;DynarchMenu._documentMouseUp=function(ev){ev||(ev=window.event);if(DynarchMenu._msupTimeout)return false;var menu=_dynarch_top.DynarchMenu._C,el,info;if(menu){el=is_ie?ev.srcElement:ev.target;for(;el&&!(el.__msh_is_dynarch_menu&&(info=el.__msh_info));el=el.parentNode);if(!el)_dynarch_top.DynarchMenu._closeOtherMenus(null);else if(info&&info.exec)info.exec();}_dynarch_top.DynarchMenu._C=null;_dynarch_top.DynarchMenu._activeItem=null;};DynarchMenu._documentMouseOver=function(ev){var menu=_dynarch_top.DynarchMenu._C,el,tmout;if(menu&&menu.config.electric){ev||(ev=window.event);el=is_ie?ev.srcElement:ev.target;for(;el&&!el.__msh_is_dynarch_menu;el=el.parentNode);if(!el||el===document.body){tmout=menu.config.electric;if(tmout===true)tmout=1;if(!_dynarch_top.DynarchMenu._T)_dynarch_top.DynarchMenu._T=_dynarch_top.setTimeout('_dynarch_top.DynarchMenu._closeOtherMenus(null); _dynarch_top.DynarchMenu._T = null;',tmout);}else _dynarch_top.DynarchMenu._clearTimeout();}};DynarchMenu._documentKeyPress=function(ev){ev||(ev=window.event);DynarchMenu._forAllMenus(function(menu){var tmp=menu._activePopup,item=tmp?tmp.active_item:null,kmap;function do_27(){if(tmp){tmp.close(true,true);if(item)item.mouseout();if(tmp.base||(tmp.parent.base&&tmp.config.context)){tmp.resetActive();tmp.active_submenu=null;_dynarch_top.DynarchMenu._activeItem=null;_dynarch_top.DynarchMenu._closeOtherMenus(null);}DynarchMenu._stopEvent(ev);}};function do_13(){if(!item)return;item.activate(true,true);if(item.action&&!item.submenu)item.exec();DynarchMenu._stopEvent(ev);};function do_ud(up){if(tmp){if(!item)item=up?tmp.getFirstItem():tmp.getLastItem();else item=up?tmp.getNextItem(item):tmp.getPrevItem(item);if(item){item.hover(false,true);tmp.active_item=item;}DynarchMenu._stopEvent(ev);}};function serveKeymap(keymap){var key=String.fromCharCode((is_ie||is_opera)?ev.keyCode:ev.charCode).toLowerCase();item=keymap[key];if(typeof item!="undefined"){item.hover(true,true);if(!item.submenu)item.exec();tmp=item.submenu;item=null;do_ud(true);DynarchMenu._stopEvent(ev);}};switch(ev.keyCode){case 27:do_27();break;case 13:do_13();if(item){tmp=item.submenu;item=null;do_ud(true);}break;case 37:if(!menu._activeKeymap)break;if(tmp.parent&&!tmp.parent.horiz)do_27();else{if(tmp.parent){tmp=tmp.parent;item=tmp.active_item;}do_ud(false);item.activate(false,true);}break;case 39:if(!menu._activeKeymap)break;if(item&&!item.parent.horiz&&item.submenu){do_13();tmp=item.submenu;item=null;do_ud(true);}else{while(tmp.parent){tmp=tmp.parent;item=tmp.active_item;}do_ud(true);item.activate(false,true);}break;case 40:case 38:if(!menu._activeKeymap)break;do_ud(ev.keyCode==40);break;default:kmap=ev.altKey?menu._globalKeymap:menu._activeKeymap;if(kmap)serveKeymap(kmap);}});};DynarchMenu.prototype.createMenuTree=function(ul,horiz){var base=!this._baseMenuInfo,a_li,div,table,i,info,li,item,tmp,ret=null,self=this,cfg=this.config,ctx=cfg.context,submenu,CE=DynarchMenu._createElement,win=base?cfg.frames.main:cfg.frames.popups,doc=win.document;if(cfg.d_profile)++DynarchMenu.profile.tree;a_li=DynarchMenu._getChildrenByTagName(ul,"li");if(a_li.length==0)return;ret=div=CE("div",null,doc);div.className=(base&&horiz)?"dynarch-horiz-menu":"dynarch-popup-menu";if(base&&horiz&&cfg.toolbar)div.className+=" dynarch-menu-toolbar";if(base&&!horiz&&!ctx)div.className+=" dynarch-popup-base-menu";if(ul.className)div.className+=" "+ul.className;tmp=["a","b","c","d"];if(cfg.scrolling)tmp.unshift("dynarch-menu-scroll");for(i=tmp.length;--i>=0;(div=CE("div",div)).className=tmp[i]);info=new DynarchMenu.MenuTree({menu:this,base:base,horiz:horiz,element:ret,active_submenu:null,active_item:null,visible:false,keymap:{},config:cfg,_T_close:null,open_left:DynarchMenu._RE_OPL.test(ul.className)});if(ul.id)info.id=ul.id;DynarchMenu.addInfo(ret,"__msh_info",info);info.table=table=CE("table",div);table.cellSpacing=0;table.cellPadding=0;tmp=CE("tbody",table);DynarchMenu._class(ret,null,cfg.className);if(base){this._globalKeymap=info.keymap;this._baseMenuInfo=info;if(ctx)ret.style.display="none";}else{ret.style.display="none";if(this.config.lazy)this.config.container.appendChild(ret);else if(this._df)this._df.appendChild(ret);else this._ca[this._ca.length]=ret;}if(horiz){info.parent=null;div=CE("tr",tmp);}else div=tmp;ret.onmouseover=win.DynarchMenu.EventHandlers.tree_onMouseOver;ret.onmouseout=win.DynarchMenu.EventHandlers.tree_onMouseOut;for(i=0;i<a_li.length;++i){li=a_li[i];if(DynarchMenu._RE_CP.test(li.className)){tmp=document.getElementById(RegExp.$1);}else{tmp=DynarchMenu._getChildrenByTagName(li,"ul");tmp=(tmp.length>0)?tmp[0]:null;}item=this.createMenuItem(li,info,horiz,!!tmp);div.appendChild(item);if(tmp){item.className+=" has-submenu";item.__msh_info.ul=ul=tmp;item.__msh_info.submenu=function(){var menu=this.menu;submenu=this.submenu=menu.createMenuTree(this.ul,false).__msh_info;submenu.parent=info;submenu.parent_item=this;menu._popupMenus[menu._popupMenus.length]=submenu;};if(!this.config.lazy)item.__msh_info.submenu();}}return ret;};DynarchMenu.prototype.destroy=function(){var a=this._baseMenuInfo.element,i,el;try{a.parentNode.removeChild(a);a=this._popupMenus;for(i=a.length;--i>=0;)try{el=a[i].element;el.parentNode.removeChild(el);el=a[i]._shadow;if(el)el.parentNode.removeChild(el);}catch(e){};a=_dynarch_top.DynarchMenu._menus;for(i=a.length;--i>=0;)if(a[i]==this)a.splice(i,1);if(a.length==0){a=DynarchMenu._eventElements;for(i=a.length;--i>=0;){el=a[i];if(el)try{DynarchMenu._removeEvent(el,(is_ie||is_opera)?"keydown":"keypress",el.DynarchMenu._documentKeyPress);DynarchMenu._removeEvent(el,"mousedown",el.DynarchMenu._documentMouseDown);DynarchMenu._removeEvent(el,"mouseup",el.DynarchMenu._documentMouseUp);DynarchMenu._removeEvent(el,"mouseover",el.DynarchMenu._documentMouseOver);}catch(e){};}}}catch(ex){};};DynarchMenu._stopEvent=function(ev){if(is_ie){ev.cancelBubble=true;ev.returnValue=false;}else{ev.preventDefault();ev.stopPropagation();}};DynarchMenu._removeEvent=function(el,evname,func){if(el.removeEventListener)el.removeEventListener(evname,func,true);else if(el.detachEvent)el.detachEvent("on"+evname,func);else el["on"+evname]=null;};DynarchMenu._addEvent=function(el,evname,func){if(el.addEventListener)el.addEventListener(evname,func,true);else if(el.attachEvent)el.attachEvent("on"+evname,func);else el["on"+evname]=func;};DynarchMenu._getChildrenByTagName=function(el,tag){var i,a=[];if(tag)tag=tag.toLowerCase();for(i=el.firstChild;i;i=i.nextSibling){if(i.nodeType!=1)continue;if(!tag||tag==i.tagName.toLowerCase())a[a.length]=i;}return a;};DynarchMenu._createElement=function(tagName,parent,doc){if(!doc){if(parent)doc=parent.ownerDocument;if(!doc)doc=document;}var el=doc.createElement(tagName);if(is_ie)el.unselectable="on";else if(is_gecko)el.style.setProperty("-moz-user-select","none","");if(parent)parent.appendChild(el);return el;};DynarchMenu._getLabel=function(el){var i,c,txt;if(el.tagName.toLowerCase()=="a"){if(is_ie){c=DynarchMenu._getChildrenByTagName(el,null);for(i=c.length;--i>=0;c[i].unselectable="on");}return el.innerHTML;}c=DynarchMenu._getChildrenByTagName(el,'div');if(c.length)return c[0];txt="";for(i=el.firstChild;i;i=i.nextSibling)if(i.nodeType==3)txt+=i.data;return txt;};DynarchMenu._getPos=function(el){if(/^body$/i.test(el.tagName))return{x:0,y:0};var SL=0,ST=0,is_div=/^div$/i.test(el.tagName),r,tmp;if(is_div&&el.scrollLeft)SL=el.scrollLeft;if(is_div&&el.scrollTop)ST=el.scrollTop;if(el.parentNode&&el.parentNode!==el.offsetParent){if(el.parentNode.scrollTop)ST+=el.parentNode.scrollTop;if(el.parentNode.scrollLeft)ST+=el.parentNode.scrollLeft;}r={x:el.offsetLeft-SL,y:el.offsetTop-ST};if(el.offsetParent){tmp=this._getPos(el.offsetParent);r.x+=tmp.x;r.y+=tmp.y;}return r;};DynarchMenu._class=function(el,del,add){if(!el)return;if(el.element)el=el.element;if(del)el.className=el.className.replace(del,' ');if(add)el.className+=" "+add;};DynarchMenu._related=function(element,ev){var related,type;if(is_ie){type=ev.type;if(type=="mouseover")related=ev.fromElement;else if(type=="mouseout")related=ev.toElement;}else related=ev.relatedTarget;for(;related;related=related.parentNode)if(related===element)return true;return false;};DynarchMenu.psLeft=function(){var d=document;return d.documentElement.scrollLeft||d.body.scrollLeft;};DynarchMenu.psTop=function(){var d=document;return d.documentElement.scrollTop||d.body.scrollTop;};DynarchMenu.preloadImages=function(filter){if(is_ie){var ai=[],hi={},i;function f(s,p){var i,t,pp=s.href;if(filter&&s.readOnly&&!filter.test(p+pp))return;if(pp)p+=pp.replace(/(\x2f?)[^\x2f]+$/,"$1");for(i=s.rules.length;--i>=0;){t=s.rules(i).style.backgroundImage;if(/url\((.*?)\)/.test(t)){t=p+RegExp.$1;if(!hi[t]){ai.push(t);hi[t]=1;}}}for(i=s.imports.length;--i>=0;)f(s.imports(i),p);};for(i=document.styleSheets.length;--i>=0;)f(document.styleSheets[i],"");document.write("<div style='display:none'>");for(i=ai.length;--i>=0;)document.write("<img src='"+ai[i]+"' />");document.write("</div>");}};DynarchMenu._infoMap=null;DynarchMenu._cleanUp=function(){var a=_dynarch_top.DynarchMenu._infoMap,i,o,p;for(i=a.length;--i>=0;){o=a[i][0];p=a[i][1];try{o[p]=null;o.parentNode.removeChild(o);o=a[i][0]=null;}catch(e){};a.splice(i,1);}_dynarch_top.DynarchMenu._infoMap=null;};DynarchMenu.addInfo=function(el,name,value){el.__msh_is_dynarch_menu=true;el[name]=value;if(is_ie){var a=_dynarch_top.DynarchMenu._infoMap;if(!a){a=_dynarch_top.DynarchMenu._infoMap=[];DynarchMenu._addEvent(_dynarch_top,"unload",_dynarch_top.DynarchMenu._cleanUp);}a[a.length]=[el,name];}};DynarchMenu.setupContext=function(ctx,tree){this.addInfo(ctx,"__msh_info2",tree);var buttons=2,b;if(tree&&tree.menu&&tree.menu.config&&tree.menu.config.ctxbutton)buttons=tree.menu.config.ctxbutton;if(/dynarch-menu-ctxbutton-([a-z]+)/.test(ctx.className)){b=RegExp.$1;buttons=((b=="left")?1:((b=="both")?3:buttons));}if(buttons&1)ctx.onclick=DynarchMenu.EventHandlers.ctx_onContextMenu;if(buttons&2)ctx[is_opera?"onmousedown":"oncontextmenu"]=DynarchMenu.EventHandlers.ctx_onContextMenu;};DynarchMenu.JSAction=function(code){this.js=code.replace(/%20/g,' ');};DynarchMenu.JSAction.prototype.exec=function(){var retval=false;eval(this.js);return retval;};DynarchMenu.LinkAction=function(url,target){if(!(target&&/\S/.test(target)))target=null;if(is_ie)url=url.replace(/^about:blank(.+)$/,"$1");this.url=url;this.target=target;};DynarchMenu.LinkAction.prototype.exec=function(){if(this.target){var tmp=document.getElementById(this.target);if(!tmp&&document.getElementsByName){tmp=document.getElementsByName(this.target);tmp=tmp.length?tmp[0]:null;}if(tmp){tmp=is_opera?tmp:tmp.contentWindow;tmp.location=this.url;}else window.open(this.url,this.target);}else window.location=this.url;return false;};DynarchMenu.DefaultAction=function(li){this.params=li;while(li&&/^([uo]l|li)$/i.test(li.tagName)){if(li.onclick){this.action=li.onclick;break;}li=li.parentNode;}};DynarchMenu.DefaultAction.prototype.exec=function(){if(!this.info.submenu){if(typeof this.action=="function")return this.action(this.info);else try{var retval=false;eval(this.action);return retval;}catch(e){};}return true;};DynarchMenu.EventHandlers={popup_resetActive:function(ev){this.__msh_info.parent.resetActive();return false;},item_onMouseOver:function(ev){ev||(ev=window.event);if(DynarchMenu._related(this,ev))return false;var item=this.__msh_info;if(!item.separator)return item.hover();else if(item.html_popup){item.parent.clearTimeout();return item.parent.resetActive(item);}},item_onMouseOut:function(ev){ev||(ev=window.event);if(DynarchMenu._related(this,ev))return false;return this.__msh_info.mouseout();},

item_onMouseDown:function(ev)
{
  ev||(ev=window.event);  
  var info=this.__msh_info,ret;
  _dynarch_top.DynarchMenu._C=info.menu;
  DynarchMenu._stopEvent(ev);
  _dynarch_top.DynarchMenu._activeItem=info;
  if(info.parent&&!info.parent.base) info.parent.closePopups();
  ret=info.activate(false,true);
  return ret;
},
tree_onMouseOver:function(ev){ev||(ev=window.event);if(!DynarchMenu._related(this,ev)){var info=this.__msh_info;if(info.parent){info.parent.resetActive(info.parent_item,"active");info.parent.active_submenu=info;}}return false;},tree_onMouseOut:function(ev){ev||(ev=window.event);if(!DynarchMenu._related(this,ev)){var info=this.__msh_info;if(!info.active_submenu)this.__msh_info.resetActive();}return false;},ctx_onContextMenu:function(ev){ev||(ev=window.event);DynarchMenu._closeOtherMenus();if(!is_opera||ev.button==2){if(DynarchMenu._msupTimeout)clearTimeout(DynarchMenu._msupTimeout);DynarchMenu._msupTimeout=setTimeout(function(){DynarchMenu._msupTimeout=null;},150);var info=this.__msh_info2;if(typeof info.submenu=="function")info.submenu();info.submenu.openContext(ev,this);setTimeout(function(){_dynarch_top.DynarchMenu._C=info.menu;},info.menu.config.timeout);DynarchMenu._stopEvent(ev);return false;}}};DynarchMenu.populateObject=function(o,props){for(var i in props)o[i]=props[i];};DynarchMenu.MenuItem=function(props){this.visible=true;this.pressed=false;DynarchMenu.populateObject(this,props);};DynarchMenu.MenuItem.prototype.disable=function(dis){if(typeof dis=="undefined")dis=true;this.disabled=dis;DynarchMenu._class(this.element,DynarchMenu._RE_DS,dis?"disabled":null);};DynarchMenu.MenuItem.prototype.display=function(dis){if(typeof dis=="undefined")dis=!this.visible;this.visible=dis;this.element.style.display=dis?"":"none";};DynarchMenu.MenuItem.prototype._exec=function(){if(!this.disabled&&!this.separator&&this.action&&!this.action.exec()){DynarchMenu._class(this.element,DynarchMenu._RE_AH);var a=this.menu._popupMenus,i;for(i=a.length;--i>=0;)a[i].close(false,true);this.menu._baseMenuInfo.close();window.status="";}};DynarchMenu.MenuItem.prototype.exec=function(){var step=this.menu.config.blink;if((this.submenu&&!this.action.explicit)||this.html_popup||!step)return this._exec();var self=this;var timer=setInterval(function(){DynarchMenu._class(self.element,DynarchMenu._RE_AH,--step&1?'active':null);if(!step){clearInterval(timer);self._exec();}},60);};DynarchMenu.MenuItem.prototype.setLabel=function(text){this.labelTD.innerHTML="<div unselectable='on'>"+text+"</div>";this.label=text;};DynarchMenu.MenuItem.prototype.hover=function(activate,instant){var menu=this.parent,el=this.element;if(this.disabled&&menu.base){menu.clearPopups(this);menu.resetActive();return;}menu.clearTimeout();window.status=this.tooltip;el.title=menu.config.tooltips?this.tooltip:"";if(typeof activate=="undefined")activate=this.submenu&&(menu.config.electric||!menu.base||menu.active_submenu);menu.clearPopups(this);if(menu.resetActive(this))DynarchMenu._clearTimeout();if(activate)this.activate(true,instant);return false;};DynarchMenu.MenuItem.prototype.activate=function(noclose,instant){if(!this.disabled){var menu=this.parent,submenu=this.submenu,el=this.element;menu.resetActive(this);if(submenu){if(typeof submenu=="function"){this.submenu();submenu=this.submenu;}if(!noclose&&!menu.config.electric&&menu.base&&submenu==menu.active_submenu){submenu.close(false,true);_dynarch_top.DynarchMenu._activeItem=null;menu.resetActive(this,"hover");}else submenu.open(el,this,instant);}}return false;};DynarchMenu.MenuItem.prototype.setClass=function(del,add){DynarchMenu._class(this.element,del,add);};DynarchMenu.MenuItem.prototype.setPressed=function(state){if(typeof state=="undefined")state=!this.pressed;this.pressed=state;this.setClass(DynarchMenu._RE_PR,state?"pressed":null);};DynarchMenu.MenuItem.prototype.mouseout=function(){var p=this.parent,s=this.submenu;if(s&&DynarchMenu._OT)clearTimeout(DynarchMenu._OT);DynarchMenu._clearTimeout();if(!s||!s.visible)p.resetActive();window.status="";return false;};DynarchMenu.MenuTree=function(props){DynarchMenu.populateObject(this,props);if(!this.base)this.hider=DynarchMenu._createHider(this.menu.config.frames.popups);};DynarchMenu.MenuTree.prototype.getNextItem=function(item){var i=item.element.nextSibling;while(i&&i.__msh_info.separator)i=i.nextSibling;if(!i){if(this.menu.config.scrolling)return null;i=item.element.parentNode.firstChild;}return i.__msh_info;};DynarchMenu.MenuTree.prototype.getPrevItem=function(item){var i=item.element.previousSibling;while(i&&i.__msh_info.separator)i=i.previousSibling;if(!i){if(this.menu.config.scrolling)return null;i=item.element.parentNode.lastChild;}return i.__msh_info;};DynarchMenu.MenuTree.prototype.resetActive=function(item,cls){item||(item=null);if(!cls)(!item||!item.html_popup)?(cls="hover"):(cls="");DynarchMenu._class(this.active_item,DynarchMenu._RE_AH);DynarchMenu._class(item,DynarchMenu._RE_AH,_dynarch_top.DynarchMenu._activeItem==item?"active":cls);var tmp=this.active_item!=item;this.active_item=item;if(item&&this.menu.config.scrolling)this._scrollIntoView(item);return tmp;};DynarchMenu.MenuTree.prototype.clearPopups=function(item){var m=this.active_submenu;if(m&&m!=item.submenu)m.close();};DynarchMenu.MenuTree.prototype.closePopups=function(){var i,m;for(i=this.getFirstItem().element;i;i=i.nextSibling){m=i.__msh_info.submenu;if(m&&typeof m!="function")m.closePopups().close(false,true);}return this;};DynarchMenu.MenuTree.prototype.clearTimeout=function(){if(this._T_close){clearTimeout(this._T_close);this._T_close=null;}};DynarchMenu.MenuTree.prototype.close=function(by_key,instant){var self=this.menu;if(this.base){self._activeKeymap=null;self._activePopup=null;}else{if(!this.visible||(this._T_close&&!instant))return false;var info=this;tmp=this.closePopups().parent;tmp.resetActive(by_key?tmp.active_item:null);DynarchMenu._class(this.active_item,DynarchMenu._RE_AH);if(!by_key)tmp.active_item=null;tmp.active_submenu=null;this.active_item=this.active_submenu=null;if(instant||(this.parent.base&&!self.config.vertical))this._close();else this._T_close=setTimeout(function(){info._close();info._T_close=null;},self.config.timeout);}};DynarchMenu.MenuTree.prototype.getFirstItem=function(){return this.horiz?this.element.firstChild.__msh_info:this.table.firstChild.firstChild.__msh_info;};DynarchMenu.MenuTree.prototype.getLastItem=function(){return this.horiz?this.element.lastChild.__msh_info:this.table.lastChild.lastChild.__msh_info;};DynarchMenu.MenuTree.prototype.openContext=function(ev,trigger){var el=ev.srcElement||ev.target,p,align=this.parent_item.align;if(!trigger)trigger=null;this.menu.target=trigger;if(el.className&&DynarchMenu._RE_CTX_AL.test(el.className))align=RegExp.$1;switch(align){case "bottom":p=DynarchMenu._getPos(el);p.y+=el.offsetHeight;break;case "right":p=DynarchMenu._getPos(el);p.x+=el.offsetWidth;break;default:p={x:ev.clientX+DynarchMenu.psLeft(),y:ev.clientY+DynarchMenu.psTop()};break;}this.open(null,null,true,p);};DynarchMenu.MenuTree.prototype.open=function(el,item,instant,pos){this.clearTimeout();DynarchMenu._clearTimeout();if(DynarchMenu._OT)clearTimeout(DynarchMenu._OT);var info=this;if(instant)this._open(el,item,pos);else DynarchMenu._OT=setTimeout(function(){info._open(el,item,pos);DynarchMenu._OT=null;},this.menu.config[this.parent.base?"baseTimeout":"timeout"]);};DynarchMenu.MenuTree.prototype._close=function(){this.element.style.display="none";this.visible=false;this.menu._activePopup=this.parent;this.menu._activeKeymap=this.parent.keymap;if(this._shadow)this._shadow.style.display="none";for(var i=this.getFirstItem().element;i;i=i.nextSibling)DynarchMenu._class(i,DynarchMenu._RE_AH);DynarchMenu._closeHider(this.hider);};DynarchMenu.$=function(func,obj,par){return function(p1){func.call(obj,par,this,p1);};};DynarchMenu.MenuTree.prototype._scrollIntoView=function(item){var diff,el=item.element,table=this.table,div=table.parentNode;if(div.scrollTop>el.offsetTop){div.scrollTop=el.offsetTop;this._scrollSetArrowState();}else{diff=el.offsetTop+el.offsetHeight-(div.scrollTop+div.offsetHeight);if(diff>0)div.scrollTop+=diff;this._scrollSetArrowState();}};DynarchMenu.MenuTree.prototype._scrollSetArrowState=function(){try{var table=this.table,div=table.parentNode,s1=this._scrollDiv1,s2=this._scrollDiv2;if(div.scrollTop==0){s1.className+=" dynarch-menu-scroll-disabled dynarch-menu-scroll-up-disabled";this._scrollStopHandler();}else s1.className=s1.className.replace(DynarchMenu._RE_SCROLL_D," ");if(div.scrollTop+div.offsetHeight==table.offsetHeight){s2.className+=" dynarch-menu-scroll-disabled dynarch-menu-scroll-down-disabled";this._scrollStopHandler();}else s2.className=s2.className.replace(DynarchMenu._RE_SCROLL_D," ");}catch(ex){};};DynarchMenu.MenuTree.prototype._scrollHandler=function(dir){this.table.parentNode.scrollTop+=this._scrollStep*dir;this._scrollSetArrowState();};DynarchMenu.MenuTree.prototype._scrollStartHandler=function(dir,div){this._scrollStep=this.menu.config.scrolling.step1;this._scrollTimer=setInterval(DynarchMenu.$(this._scrollHandler,this,dir),this.menu.config.scrolling.speed);div.className+=" dynarch-menu-scroll-hover "+(dir>0?"dynarch-menu-scroll-down-hover":"dynarch-menu-scroll-up-hover");};DynarchMenu.MenuTree.prototype._scrollStopHandler=function(undef,div){if(this._scrollTimer){clearInterval(this._scrollTimer);this._scrollTimer=null;}div.className=div.className.replace(DynarchMenu._RE_SCROLL_H," ");};DynarchMenu.MenuTree.prototype._scrollDoubleSpeed=function(dbl){this._scrollStep=this.menu.config.scrolling[dbl?"step2":"step1"];};DynarchMenu.MenuTree.prototype._setupScroll=function(){var scroll_div=this.table.parentNode;if(!this._hasScroll){var CE=DynarchMenu._createElement;this._hasScroll=true;var s1=CE("div",null,document);s1.className="dynarch-menu-scroll-up";s1.innerHTML="&nbsp;";if(is_ie)s1.style.width=this.table.offsetWidth+"px";var p=scroll_div.parentNode;p.insertBefore(s1,scroll_div);var s2=s1.cloneNode(true);s2.className="dynarch-menu-scroll-down";p.appendChild(s2);var AI=DynarchMenu.addInfo;AI(s1,"onmouseover",DynarchMenu.$(this._scrollStartHandler,this,-1));AI(s2,"onmouseover",DynarchMenu.$(this._scrollStartHandler,this,1));var tmp=DynarchMenu.$(this._scrollStopHandler,this);AI(s1,"onmouseout",tmp);AI(s2,"onmouseout",tmp);tmp=DynarchMenu.$(this._scrollDoubleSpeed,this,true);AI(s1,"onmousedown",tmp);AI(s2,"onmousedown",tmp);tmp=DynarchMenu.$(this._scrollDoubleSpeed,this,false);AI(s1,"onmouseup",tmp);AI(s2,"onmouseup",tmp);this._scrollDiv1=s1;this._scrollDiv2=s2;}this._showScroll(true);scroll_div.scrollTop=0;this._scrollDiv1.className+=" dynarch-menu-scroll-up-disabled";this._scrollDiv2.className=this._scrollDiv2.className.replace(DynarchMenu._RE_SCROLL_D," ");};DynarchMenu.MenuTree.prototype._showScroll=function(disp){if(this._hasScroll){var p=this.table.parentNode.parentNode;p.firstChild.style.display=disp?"":"none";p.lastChild.style.display=disp?"":"none";}};DynarchMenu.MenuTree.prototype._open=function(el,item,pos){this.menu.config.onPopup.call(this,this.menu.target,item,pos);var m=this.element,self=this.menu,cfg=self.config,win=cfg.frames.popups,p=el?win.DynarchMenu._getPos(el):pos,pe,base=this.parent?this.parent.base:false,dx=base?cfg.basedx:cfg.dx,dy=base?cfg.basedy:cfg.dy,horiz=this.parent?this.parent.horiz:false,tmp,s,vw,sw;if(!el)el={offsetHeight:0,offsetWidth:0};if(base&&cfg.crossFrames){if(cfg.vertical){p.x=win.DynarchMenu.psLeft();p.y+=win.DynarchMenu.psTop();}else p.y=win.DynarchMenu.psTop();}else if(self._fixed&&!is_ie&&base){p.x+=win.DynarchMenu.psLeft();p.y+=win.DynarchMenu.psTop();}pe={x:p.x,y:p.y};_dynarch_top.DynarchMenu._C=self;DynarchMenu._closeOtherMenus(self);if((!base||cfg.vertical)&&item)item.parent.closePopups();if(!(base&&cfg.crossFrames)){if(horiz)p.y+=el.offsetHeight;else{if(!is_khtml){p.x+=el.offsetWidth;}else if(el){p=win.DynarchMenu._getPos(el.lastChild);p.x+=el.lastChild.offsetWidth;p.y-=1;}}}vw=win.DynarchMenu.getWinSize();vw.x+=win.DynarchMenu.psLeft();vw.y+=win.DynarchMenu.psTop();sw=cfg.shadows||[0,0];s=m.style;if(is_ie)s.position="absolute";s.visibility="hidden";s.display="block";var scroll_div=this.table.parentNode;if(cfg.scrolling){this._showScroll(false);scroll_div.style.height="";scroll_div.style.width="";}if(this.open_left||p.x+m.offsetWidth>vw.x){p.x=pe.x-m.offsetWidth+(horiz?el.offsetWidth:2);dx=-dx;}if(p.y+m.offsetHeight>vw.y&&pe.y>m.offsetHeight){p.y=pe.y-m.offsetHeight+(horiz?0:(win.DynarchMenu._getPos(m).y+m.offsetHeight-win.DynarchMenu._getPos(this.getLastItem().element).y));dy=-dy;}else if(!horiz)p.y-=win.DynarchMenu._getPos(this.getFirstItem().element).y-win.DynarchMenu._getPos(m).y;if(p.x+m.offsetWidth+sw[0]>vw.x)p.x-=sw[0];if(p.y<0)p.y=0;var y_low=p.y+m.offsetHeight-vw.y;if(cfg.scrolling&&y_low>0){var height=scroll_div.offsetHeight-y_low;height-=m.offsetHeight-scroll_div.offsetHeight;height-=sw[1];if(sw[3])height-=sw[3];height-=24;if(height<vw.y*0.75){y_low=Math.floor(vw.y*0.75-height);height+=y_low;p.y-=y_low;}if(is_ie)scroll_div.style.width=this.table.offsetWidth+"px";if(height<scroll_div.offsetHeight){scroll_div.style.height=height+"px";this._setupScroll();}}p.x+=dx;p.y+=dy;s.left=p.x+"px";s.top=p.y+"px";DynarchMenu._setupHider(this.hider,p.x,p.y,m.offsetWidth+sw[0],m.offsetHeight+sw[1]);if(this.parent){this.parent.active_submenu=this;this.parent.resetActive(item,"active");}self._activePopup=this;self._activeKeymap=this.keymap;tmp=this._shadow;if(cfg.shadows){if(!tmp){var SS=cfg.smoothShadow;this._shadow=tmp=DynarchMenu._createElement((SS&&!is_ie)?"img":"div",null,cfg.container.ownerDocument);if(SS)tmp.src=_dynarch_menu_shadow.src;tmp.className="dynarch-menu-shadow";DynarchMenu.addInfo(tmp,'__msh_info',this);if(is_ie)tmp.style.position="absolute";if(SS&&is_ie&&!is_ie5){tmp.className="dynarch-IE6-shadow";tmp.runtimeStyle.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+_dynarch_menu_shadow.src+"',sizingMethod='scale')";}tmp.style.width="2px";tmp.style.height="2px";cfg.container.appendChild(tmp);}s=tmp.style;if(sw.length>2){s.left=p.x+sw[0]+"px";s.top=p.y+sw[1]+"px";s.width=m.offsetWidth+sw[2]+"px";s.height=m.offsetHeight+sw[3]+"px";}else{s.left=p.x+sw[0]+"px";s.top=p.y+sw[1]+"px";s.width=m.offsetWidth+"px";s.height=m.offsetHeight+"px";}s.display="block";}this.visible=true;m.style.visibility="visible";};DynarchMenu.getWinSize=function(){if(is_gecko){return{x:window.innerWidth,y:window.innerHeight};}if(is_opera)return{x:window.innerWidth,y:window.innerHeight};if(is_ie){if(!document.compatMode||document.compatMode=="BackCompat")return{x:document.body.clientWidth,y:document.body.clientHeight};else return{x:document.documentElement.clientWidth,y:document.documentElement.clientHeight};}var div=document.createElement("div"),s=div.style;s.position="absolute";s.bottom=s.right="0px";document.body.appendChild(div);s={x:div.offsetLeft,y:div.offsetTop};document.body.removeChild(div);return s;};DynarchMenu.getCA=function(el,name){return el.getAttribute("DynarchMenu:"+name);};DynarchMenu._nfo={product:"hmenu-2.9",licensee:"Dynarch.com user: dedys",license_key:"linkware-7293688",purchase_date:"Mon Dec 11 19:48:52 2006 GMT",license_type:"linkware"

};
