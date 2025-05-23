var decimalPoint = '.';
var dateSeparator = '/';

//var keybYN = new keybEdit('yn','Valid values are \'Y\' or \'N\'.');
var keybNumeric = new keybEdit('01234567890','Integer input only.');
var keybAlpha = new keybEdit('abcdefghijklmnopqurstuvwxy ','Alpha input only.');
var keybAlphaNumeric = new keybEdit('abcdefghijklmnopqurstuvwxy01234567890 ','Alpha-numeric input only.');
//var keybYNNM = new keybEdit('yn');
//var keybAlphaNM = new keybEdit('abcdefghijklmnopqurstuvwxy');
//var keybAlphaNumericNM = new keybEdit('abcdefghijklmnopqurstuvwxy01234567890');
var keybDecimalNM = new keybEdit('01234567890'+decimalPoint,'Decimal input only');
//var keybDateNM = new keybEdit('01234567890'+dateSeparator);

var specialKeyCode = new Array(8,9,46,37,39,36,35,33,34,38,40,27,13,16,17,18,91,92,93);

function keybEdit(strValid, strMsg) {
	/*	Function:		keybEdit
		Creation Date:	October 11, 2001
		Programmer:		Edmond Woychowsky
	*/

	//	Variables
	var reWork = new RegExp('[a-z]','gi');		//	Regular expression\

	//	Properties
	if(reWork.test(strValid))
		this.valid = strValid.toLowerCase() + strValid.toUpperCase();
	else
		this.valid = strValid;

	if((strMsg == null) || (typeof(strMsg) == 'undefined'))
		this.message = 'Invalid input!';
	else
		this.message = strMsg;

	//	Methods
	this.getValid = function () { return this.valid.toString(); }
	this.getMessage = function () { return this.message; }
}

 function editKeyBoardInteger(e) {
	strWork = keybNumeric.getValid();
	strMsg = '';							// Error message
	blnValidChar = false;					// Valid character flag

	if (e == null) e=window.event;

	var keyCode = e.keyCode;
	if ( keyCode == 0 ) // as it might on Moz
		keyCode = e.charCode;
	if ( keyCode == 0 ) // unlikely to get here
		keyCode = e.which;


	if ( e.charCode != null && e.charCode == 0 ) {
	  for(i=0;i < specialKeyCode.length;i++)
		if(keyCode== specialKeyCode[i]) {
			return;
		}
	}

	// Part 1: Validate input
	if(!blnValidChar)
		for(i=0;i < strWork.length;i++)
			if(keyCode== strWork.charCodeAt(i)) {
				blnValidChar = true;
				break;
			}

	// Part 2: Build error message
	if(!blnValidChar) {
		//alert('Error: Numeric input only.');

		if( document.all){
			e.keyCode = 0;
		}
		else{
			e.preventDefault();
		}
		//e.returnValue = false;		// Clear invalid character
		this.focus();						// Set focus
	}
}

 function editKeyBoardNumeric(e) 
 {
	strWork = keybDecimalNM.getValid();
	strMsg = '';							// Error message
	blnValidChar = false;					// Valid character flag

	if (e == null) e=window.event;

	var keyCode = e.keyCode;
	if ( keyCode == 0 ) // as it might on Moz
		keyCode = e.charCode;
	if ( keyCode == 0 ) // unlikely to get here
		keyCode = e.which;


	if ( e.charCode != null && e.charCode == 0 ) {
	  for(i=0;i < specialKeyCode.length;i++)
		if(keyCode== specialKeyCode[i]) {
			return;
		}
	}

	// Part 1: Validate input
	if(!blnValidChar)
		for(i=0;i < strWork.length;i++)
    {
      if((this.decimalPointExist == null) || (typeof(this.decimalPointExist) == 'undefined'))
      {
        if(keyCode== ".") this.decimalPointExist = true;
        if(keyCode== strWork.charCodeAt(i)) 
        {
  				blnValidChar = true;
  				break;
  			}
      }
      else
      {
        if(keyCode== ".") this.decimalPointExist = true;
        if(keyCode== strWork.charCodeAt(i)) 
        {
  				blnValidChar = true;
  				break;
  			}
      }
    }

	// Part 2: Build error message
	if(!blnValidChar) {
		//alert('Error: Numeric input only.');

		if( document.all){
			e.keyCode = 0;
		}
		else{
			e.preventDefault();
		}
		//e.returnValue = false;		// Clear invalid character
		this.focus();						// Set focus
	}
}

function maskEdit(txtField, func ) 
{
	txtField.onkeypress = func;
}


/**************************************************************
 THIS FUNCTIONS BELOW IS REGULAR EXPRESSION VALIDATOR
 By: Dedy S.
 **************************************************************/ 
function validateDate( strValue ) 
{
  //validate format date is: YYYY-MM-DD
  //termasuk mengecek bulan dan tanggal apakah valid atau tidak
  if (strValue == "") return false;
  var objRegExp = /^\d{4}(\-)\d{1,2}\1\d{1,2}$/
  //check to see if in correct format
  if(!objRegExp.test(strValue)) return false;
  strDateParts = strValue.split("-");
  y = parseInt(strDateParts[0]);
  m = parseInt(strDateParts[1],10);
  d = parseInt(strDateParts[2],10);
  if ((m > 0) && (m <= 12) && (d > 0) && (d <= 31))
  {
    isKabisat = false;
    if (y % 4 == 0)
      if (y % 100 == 0)
        if (y % 400 == 0) isKabisat = true; else;
      else isKabisat=true;
    maxDay = 31;
    switch (m)
    {
      case 2: if (isKabisat) maxDay = 29; else maxDay=28;
              break;
      case 4:
      case 6:
      case 9:
      case 11:maxDay = 30;
              break;
    }
    if ((d > 0) && (d <= maxDay)) return true;
  }
  return false;
}

function validateInteger( strValue ) 
{
  var objRegExp  = /(^-?\d\d*$)/;
  return objRegExp.test(strValue);
}

function  validateNumeric( strValue ) 
{
  var objRegExp  =  /(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/;
  //check for numeric characters
  return objRegExp.test(strValue);
}

function validateEmail( strValue) 
{
  var objRegExp  = /(^[a-z]([a-z_\.]*)@([a-z_\.]*)([.][a-z]{3})$)|(^[a-z]([a-z_\.]*)@([a-z_\.]*)(\.[a-z]{2})(\.[a-z]{2})*$)/i;
  //check for valid email
  return objRegExp.test(strValue);
}


function validateString( strValue ) 
{
   var strTemp = strValue;
   //strTemp = trimAll(strTemp);
   if (strTemp.length > 0) return true;
   return false;
}

function trimAll( strValue ) 
{
  var objRegExp = /^(\s*)$/;
  //check for all spaces
  if(objRegExp.test(strValue)) 
  {
    strValue = strValue.replace(objRegExp, '');
    if( strValue.length == 0) return strValue;
  }
  //check for leading & trailing spaces
  objRegExp = /^(\s*)([\W\w]*)(\b\s*$)/;
  if(objRegExp.test(strValue)) 
  {
    //remove leading and trailing whitespace characters
    strValue = strValue.replace(objRegExp, '$2');
  }
  return strValue;
}

//CALENDAR JS
/*  Copyright Mihai Bazon, 2002, 2003  |  http://dynarch.com/mishoo/
 * ------------------------------------------------------------------
 *
 * The DHTML Calendar, version 0.9.5 "Your favorite time, bis"
 *
 * Details and latest version at:
 * http://dynarch.com/mishoo/calendar.epl
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 */

/** The Calendar object constructor. */
Calendar = function (mondayFirst, dateStr, onSelected, onClose) {
	// member variables
	this.activeDiv = null;
	this.currentDateEl = null;
	this.getDateStatus = null;
	this.timeout = null;
	this.onSelected = onSelected || null;
	this.onClose = onClose || null;
	this.dragging = false;
	this.hidden = false;
	this.minYear = 1970;
	this.maxYear = 2050;
	this.dateFormat = Calendar._TT["DEF_DATE_FORMAT"];
	this.ttDateFormat = Calendar._TT["TT_DATE_FORMAT"];
	this.isPopup = true;
	this.weekNumbers = true;
	this.mondayFirst = mondayFirst;
	this.dateStr = dateStr;
	this.ar_days = null;
	this.showsTime = false;
	this.time24 = true;
	// HTML elements
	this.table = null;
	this.element = null;
	this.tbody = null;
	this.firstdayname = null;
	// Combo boxes
	this.monthsCombo = null;
	this.yearsCombo = null;
	this.hilitedMonth = null;
	this.activeMonth = null;
	this.hilitedYear = null;
	this.activeYear = null;
	// Information
	this.dateClicked = false;

	// one-time initializations
	if (typeof Calendar._SDN == "undefined") {
		// table of short day names
		if (typeof Calendar._SDN_len == "undefined")
			Calendar._SDN_len = 3;
		var ar = new Array();
		for (var i = 8; i > 0;) {
			ar[--i] = Calendar._DN[i].substr(0, Calendar._SDN_len);
		}
		Calendar._SDN = ar;
		// table of short month names
		if (typeof Calendar._SMN_len == "undefined")
			Calendar._SMN_len = 3;
		ar = new Array();
		for (var i = 12; i > 0;) {
			ar[--i] = Calendar._MN[i].substr(0, Calendar._SMN_len);
		}
		Calendar._SMN = ar;
	}
};

// ** constants

/// "static", needed for event handlers.
Calendar._C = null;

/// detect a special case of "web browser"
Calendar.is_ie = ( /msie/i.test(navigator.userAgent) &&
		   !/opera/i.test(navigator.userAgent) );

/// detect Opera browser
Calendar.is_opera = /opera/i.test(navigator.userAgent);

/// detect KHTML-based browsers
Calendar.is_khtml = /Konqueror|Safari|KHTML/i.test(navigator.userAgent);

// BEGIN: UTILITY FUNCTIONS; beware that these might be moved into a separate
//        library, at some point.

Calendar.getAbsolutePos = function(el) {
	var SL = 0, ST = 0;
	var is_div = /^div$/i.test(el.tagName);
	if (is_div && el.scrollLeft)
		SL = el.scrollLeft;
	if (is_div && el.scrollTop)
		ST = el.scrollTop;
	var r = { x: el.offsetLeft - SL, y: el.offsetTop - ST };
	if (el.offsetParent) {
		var tmp = Calendar.getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
};

Calendar.isRelated = function (el, evt) {
	var related = evt.relatedTarget;
	if (!related) {
		var type = evt.type;
		if (type == "mouseover") {
			related = evt.fromElement;
		} else if (type == "mouseout") {
			related = evt.toElement;
		}
	}
	while (related) {
		if (related == el) {
			return true;
		}
		related = related.parentNode;
	}
	return false;
};

Calendar.removeClass = function(el, className) {
	if (!(el && el.className)) {
		return;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != className) {
			ar[ar.length] = cls[i];
		}
	}
	el.className = ar.join(" ");
};

Calendar.addClass = function(el, className) {
	Calendar.removeClass(el, className);
	el.className += " " + className;
};

Calendar.getElement = function(ev) {
	if (Calendar.is_ie) {
		return window.event.srcElement;
	} else {
		return ev.currentTarget;
	}
};

Calendar.getTargetElement = function(ev) {
	if (Calendar.is_ie) {
		return window.event.srcElement;
	} else {
		return ev.target;
	}
};

Calendar.stopEvent = function(ev) {
	ev || (ev = window.event);
	if (Calendar.is_ie) {
		ev.cancelBubble = true;
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
	return false;
};

Calendar.addEvent = function(el, evname, func) {
	if (el.attachEvent) { // IE
		el.attachEvent("on" + evname, func);
	} else if (el.addEventListener) { // Gecko / W3C
		el.addEventListener(evname, func, true);
	} else {
		el["on" + evname] = func;
	}
};

Calendar.removeEvent = function(el, evname, func) {
	if (el.detachEvent) { // IE
		el.detachEvent("on" + evname, func);
	} else if (el.removeEventListener) { // Gecko / W3C
		el.removeEventListener(evname, func, true);
	} else {
		el["on" + evname] = null;
	}
};

Calendar.createElement = function(type, parent) {
	var el = null;
	if (document.createElementNS) {
		// use the XHTML namespace; IE won't normally get here unless
		// _they_ "fix" the DOM2 implementation.
		el = document.createElementNS("http://www.w3.org/1999/xhtml", type);
	} else {
		el = document.createElement(type);
	}
	if (typeof parent != "undefined") {
		parent.appendChild(el);
	}
	return el;
};

// END: UTILITY FUNCTIONS

// BEGIN: CALENDAR STATIC FUNCTIONS

/** Internal -- adds a set of events to make some element behave like a button. */
Calendar._add_evs = function(el) {
	with (Calendar) {
		addEvent(el, "mouseover", dayMouseOver);
		addEvent(el, "mousedown", dayMouseDown);
		addEvent(el, "mouseout", dayMouseOut);
		if (is_ie) {
			addEvent(el, "dblclick", dayMouseDblClick);
			el.setAttribute("unselectable", true);
		}
	}
};

Calendar.findMonth = function(el) {
	if (typeof el.month != "undefined") {
		return el;
	} else if (typeof el.parentNode.month != "undefined") {
		return el.parentNode;
	}
	return null;
};

Calendar.findYear = function(el) {
	if (typeof el.year != "undefined") {
		return el;
	} else if (typeof el.parentNode.year != "undefined") {
		return el.parentNode;
	}
	return null;
};

Calendar.showMonthsCombo = function () {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	var cal = cal;
	var cd = cal.activeDiv;
	var mc = cal.monthsCombo;
	if (cal.hilitedMonth) {
		Calendar.removeClass(cal.hilitedMonth, "hilite");
	}
	if (cal.activeMonth) {
		Calendar.removeClass(cal.activeMonth, "active");
	}
	var mon = cal.monthsCombo.getElementsByTagName("div")[cal.date.getMonth()];
	Calendar.addClass(mon, "active");
	cal.activeMonth = mon;
	var s = mc.style;
	s.display = "block";
	if (cd.navtype < 0)
		s.left = cd.offsetLeft + "px";
	else
		s.left = (cd.offsetLeft + cd.offsetWidth - mc.offsetWidth) + "px";
	s.top = (cd.offsetTop + cd.offsetHeight) + "px";
};

Calendar.showYearsCombo = function (fwd) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	var cal = cal;
	var cd = cal.activeDiv;
	var yc = cal.yearsCombo;
	if (cal.hilitedYear) {
		Calendar.removeClass(cal.hilitedYear, "hilite");
	}
	if (cal.activeYear) {
		Calendar.removeClass(cal.activeYear, "active");
	}
	cal.activeYear = null;
	var Y = cal.date.getFullYear() + (fwd ? 1 : -1);
	var yr = yc.firstChild;
	var show = false;
	for (var i = 12; i > 0; --i) {
		if (Y >= cal.minYear && Y <= cal.maxYear) {
			yr.firstChild.data = Y;
			yr.year = Y;
			yr.style.display = "block";
			show = true;
		} else {
			yr.style.display = "none";
		}
		yr = yr.nextSibling;
		Y += fwd ? 2 : -2;
	}
	if (show) {
		var s = yc.style;
		s.display = "block";
		if (cd.navtype < 0)
			s.left = cd.offsetLeft + "px";
		else
			s.left = (cd.offsetLeft + cd.offsetWidth - yc.offsetWidth) + "px";
		s.top = (cd.offsetTop + cd.offsetHeight) + "px";
	}
};

// event handlers

Calendar.tableMouseUp = function(ev) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	if (cal.timeout) {
		clearTimeout(cal.timeout);
	}
	var el = cal.activeDiv;
	if (!el) {
		return false;
	}
	var target = Calendar.getTargetElement(ev);
	ev || (ev = window.event);
	Calendar.removeClass(el, "active");
	if (target == el || target.parentNode == el) {
		Calendar.cellClick(el, ev);
	}
	var mon = Calendar.findMonth(target);
	var date = null;
	if (mon) {
		date = new Date(cal.date);
		if (mon.month != date.getMonth()) {
			date.setMonth(mon.month);
			cal.setDate(date);
			cal.dateClicked = false;
			cal.callHandler();
		}
	} else {
		var year = Calendar.findYear(target);
		if (year) {
			date = new Date(cal.date);
			if (year.year != date.getFullYear()) {
				date.setFullYear(year.year);
				cal.setDate(date);
				cal.dateClicked = false;
				cal.callHandler();
			}
		}
	}
	with (Calendar) {
		removeEvent(document, "mouseup", tableMouseUp);
		removeEvent(document, "mouseover", tableMouseOver);
		removeEvent(document, "mousemove", tableMouseOver);
		cal._hideCombos();
		_C = null;
		return stopEvent(ev);
	}
};

Calendar.tableMouseOver = function (ev) {
	var cal = Calendar._C;
	if (!cal) {
		return;
	}
	var el = cal.activeDiv;
	var target = Calendar.getTargetElement(ev);
	if (target == el || target.parentNode == el) {
		Calendar.addClass(el, "hilite active");
		Calendar.addClass(el.parentNode, "rowhilite");
	} else {
		if (typeof el.navtype == "undefined" || (el.navtype != 50 && (el.navtype == 0 || Math.abs(el.navtype) > 2)))
			Calendar.removeClass(el, "active");
		Calendar.removeClass(el, "hilite");
		Calendar.removeClass(el.parentNode, "rowhilite");
	}
	ev || (ev = window.event);
	if (el.navtype == 50 && target != el) {
		var pos = Calendar.getAbsolutePos(el);
		var w = el.offsetWidth;
		var x = ev.clientX;
		var dx;
		var decrease = true;
		if (x > pos.x + w) {
			dx = x - pos.x - w;
			decrease = false;
		} else
			dx = pos.x - x;

		if (dx < 0) dx = 0;
		var range = el._range;
		var current = el._current;
		var count = Math.floor(dx / 10) % range.length;
		for (var i = range.length; --i >= 0;)
			if (range[i] == current)
				break;
		while (count-- > 0)
			if (decrease) {
				if (!(--i in range))
					i = range.length - 1;
			} else if (!(++i in range))
				i = 0;
		var newval = range[i];
		el.firstChild.data = newval;

		cal.onUpdateTime();
	}
	var mon = Calendar.findMonth(target);
	if (mon) {
		if (mon.month != cal.date.getMonth()) {
			if (cal.hilitedMonth) {
				Calendar.removeClass(cal.hilitedMonth, "hilite");
			}
			Calendar.addClass(mon, "hilite");
			cal.hilitedMonth = mon;
		} else if (cal.hilitedMonth) {
			Calendar.removeClass(cal.hilitedMonth, "hilite");
		}
	} else {
		if (cal.hilitedMonth) {
			Calendar.removeClass(cal.hilitedMonth, "hilite");
		}
		var year = Calendar.findYear(target);
		if (year) {
			if (year.year != cal.date.getFullYear()) {
				if (cal.hilitedYear) {
					Calendar.removeClass(cal.hilitedYear, "hilite");
				}
				Calendar.addClass(year, "hilite");
				cal.hilitedYear = year;
			} else if (cal.hilitedYear) {
				Calendar.removeClass(cal.hilitedYear, "hilite");
			}
		} else if (cal.hilitedYear) {
			Calendar.removeClass(cal.hilitedYear, "hilite");
		}
	}
	return Calendar.stopEvent(ev);
};

Calendar.tableMouseDown = function (ev) {
	if (Calendar.getTargetElement(ev) == Calendar.getElement(ev)) {
		return Calendar.stopEvent(ev);
	}
};

Calendar.calDragIt = function (ev) {
	var cal = Calendar._C;
	if (!(cal && cal.dragging)) {
		return false;
	}
	var posX;
	var posY;
	if (Calendar.is_ie) {
		posY = window.event.clientY + document.body.scrollTop;
		posX = window.event.clientX + document.body.scrollLeft;
	} else {
		posX = ev.pageX;
		posY = ev.pageY;
	}
	cal.hideShowCovered();
	var st = cal.element.style;
	st.left = (posX - cal.xOffs) + "px";
	st.top = (posY - cal.yOffs) + "px";
	return Calendar.stopEvent(ev);
};

Calendar.calDragEnd = function (ev) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	cal.dragging = false;
	with (Calendar) {
		removeEvent(document, "mousemove", calDragIt);
		removeEvent(document, "mouseover", stopEvent);
		removeEvent(document, "mouseup", calDragEnd);
		tableMouseUp(ev);
	}
	cal.hideShowCovered();
};

Calendar.dayMouseDown = function(ev) {
	var el = Calendar.getElement(ev);
	if (el.disabled) {
		return false;
	}
	var cal = el.calendar;
	cal.activeDiv = el;
	Calendar._C = cal;
	if (el.navtype != 300) with (Calendar) {
		if (el.navtype == 50)
			el._current = el.firstChild.data;
		addClass(el, "hilite active");
		addEvent(document, "mouseover", tableMouseOver);
		addEvent(document, "mousemove", tableMouseOver);
		addEvent(document, "mouseup", tableMouseUp);
	} else if (cal.isPopup) {
		cal._dragStart(ev);
	}
	if (el.navtype == -1 || el.navtype == 1) {
		if (cal.timeout) clearTimeout(cal.timeout);
		cal.timeout = setTimeout("Calendar.showMonthsCombo()", 250);
	} else if (el.navtype == -2 || el.navtype == 2) {
		if (cal.timeout) clearTimeout(cal.timeout);
		cal.timeout = setTimeout((el.navtype > 0) ? "Calendar.showYearsCombo(true)" : "Calendar.showYearsCombo(false)", 250);
	} else {
		cal.timeout = null;
	}
	return Calendar.stopEvent(ev);
};

Calendar.dayMouseDblClick = function(ev) {
	Calendar.cellClick(Calendar.getElement(ev), ev || window.event);
	if (Calendar.is_ie) {
		document.selection.empty();
	}
};

Calendar.dayMouseOver = function(ev) {
	var el = Calendar.getElement(ev);
	if (Calendar.isRelated(el, ev) || Calendar._C || el.disabled) {
		return false;
	}
	if (el.ttip) {
		if (el.ttip.substr(0, 1) == "_") {
			var date = null;
			with (el.calendar.date) {
				date = new Date(getFullYear(), getMonth(), el.caldate);
			}
			el.ttip = date.print(el.calendar.ttDateFormat) + el.ttip.substr(1);
		}
		el.calendar.tooltips.firstChild.data = el.ttip;
	}
	if (el.navtype != 300) {
		Calendar.addClass(el, "hilite");
		if (el.caldate) {
			Calendar.addClass(el.parentNode, "rowhilite");
		}
	}
	return Calendar.stopEvent(ev);
};

Calendar.dayMouseOut = function(ev) {
	with (Calendar) {
		var el = getElement(ev);
		if (isRelated(el, ev) || _C || el.disabled) {
			return false;
		}
		removeClass(el, "hilite");
		if (el.caldate) {
			removeClass(el.parentNode, "rowhilite");
		}
		el.calendar.tooltips.firstChild.data = _TT["SEL_DATE"];
		return stopEvent(ev);
	}
};

/**
 *  A generic "click" handler :) handles all types of buttons defined in this
 *  calendar.
 */
Calendar.cellClick = function(el, ev) {
	var cal = el.calendar;
	var closing = false;
	var newdate = false;
	var date = null;
	if (typeof el.navtype == "undefined") {
		Calendar.removeClass(cal.currentDateEl, "selected");
		Calendar.addClass(el, "selected");
		closing = (cal.currentDateEl == el);
		if (!closing) {
			cal.currentDateEl = el;
		}
		cal.date.setDate(el.caldate);
		date = cal.date;
		newdate = true;
		// a date was clicked
		cal.dateClicked = true;
	} else {
		if (el.navtype == 200) {
			Calendar.removeClass(el, "hilite");
			cal.callCloseHandler();
			return;
		}
		date = (el.navtype == 0) ? new Date() : new Date(cal.date);
		// unless "today" was clicked, we assume no date was clicked so
		// the selected handler will know not to close the calenar when
		// in single-click mode.
		// cal.dateClicked = (el.navtype == 0);
		cal.dateClicked = false;
		var year = date.getFullYear();
		var mon = date.getMonth();
		function setMonth(m) {
			var day = date.getDate();
			var max = date.getMonthDays(m);
			if (day > max) {
				date.setDate(max);
			}
			date.setMonth(m);
		};
		switch (el.navtype) {
		    case 400:
			Calendar.removeClass(el, "hilite");
			var text = Calendar._TT["ABOUT"];
			if (typeof text != "undefined") {
				text += cal.showsTime ? Calendar._TT["ABOUT_TIME"] : "";
			} else {
				// FIXME: this should be removed as soon as lang files get modified!
				text = "Help and about box text is not translated into this language.\n" +
					"If you know this language and you feel generous please update\n" +
					"the corresponding file in \"lang\" subdir to match calendar-en.js\n" +
					"and send it back to <mishoo@infoiasi.ro> to get it into the distribution  ;-)\n\n" +
					"Thank you!\n" +
					"http://dynarch.com/mishoo/calendar.epl\n";
			}
			alert(text);
			return;
		    case -2:
			if (year > cal.minYear) {
				date.setFullYear(year - 1);
			}
			break;
		    case -1:
			if (mon > 0) {
				setMonth(mon - 1);
			} else if (year-- > cal.minYear) {
				date.setFullYear(year);
				setMonth(11);
			}
			break;
		    case 1:
			if (mon < 11) {
				setMonth(mon + 1);
			} else if (year < cal.maxYear) {
				date.setFullYear(year + 1);
				setMonth(0);
			}
			break;
		    case 2:
			if (year < cal.maxYear) {
				date.setFullYear(year + 1);
			}
			break;
		    case 100:
			cal.setMondayFirst(!cal.mondayFirst);
			return;
		    case 50:
			var range = el._range;
			var current = el.firstChild.data;
			for (var i = range.length; --i >= 0;)
				if (range[i] == current)
					break;
			if (ev && ev.shiftKey) {
				if (!(--i in range))
					i = range.length - 1;
			} else if (!(++i in range))
				i = 0;
			var newval = range[i];
			el.firstChild.data = newval;
			cal.onUpdateTime();
			return;
		    case 0:
			// TODAY will bring us here
			if ((typeof cal.getDateStatus == "function") && cal.getDateStatus(date, date.getFullYear(), date.getMonth(), date.getDate())) {
				// remember, "date" was previously set to new
				// Date() if TODAY was clicked; thus, it
				// contains today date.
				return false;
			}
			break;
		}
		if (!date.equalsTo(cal.date)) {
			cal.setDate(date);
			newdate = true;
		}
	}
	if (newdate) {
		cal.callHandler();
	}
	if (closing) {
		Calendar.removeClass(el, "hilite");
		cal.callCloseHandler();
	}
};

// END: CALENDAR STATIC FUNCTIONS

// BEGIN: CALENDAR OBJECT FUNCTIONS

/**
 *  This function creates the calendar inside the given parent.  If _par is
 *  null than it creates a popup calendar inside the BODY element.  If _par is
 *  an element, be it BODY, then it creates a non-popup calendar (still
 *  hidden).  Some properties need to be set before calling this function.
 */
Calendar.prototype.create = function (_par) {
	var parent = null;
	if (! _par) {
		// default parent is the document body, in which case we create
		// a popup calendar.
		parent = document.getElementsByTagName("body")[0];
		this.isPopup = true;
	} else {
		parent = _par;
		this.isPopup = false;
	}
	this.date = this.dateStr ? new Date(this.dateStr) : new Date();

	var table = Calendar.createElement("table");
	this.table = table;
	table.cellSpacing = 0;
	table.cellPadding = 0;
	table.calendar = this;
	Calendar.addEvent(table, "mousedown", Calendar.tableMouseDown);

	var div = Calendar.createElement("div");
	this.element = div;
	div.className = "calendar";
	if (this.isPopup) {
		div.style.position = "absolute";
		div.style.display = "none";
	}
	div.appendChild(table);

	var thead = Calendar.createElement("thead", table);
	var cell = null;
	var row = null;

	var cal = this;
	var hh = function (text, cs, navtype) {
		cell = Calendar.createElement("td", row);
		cell.colSpan = cs;
		cell.className = "button";
		if (navtype != 0 && Math.abs(navtype) <= 2)
			cell.className += " nav";
		Calendar._add_evs(cell);
		cell.calendar = cal;
		cell.navtype = navtype;
		if (text.substr(0, 1) != "&") {
			cell.appendChild(document.createTextNode(text));
		}
		else {
			// FIXME: dirty hack for entities
			cell.innerHTML = text;
		}
		return cell;
	};

	row = Calendar.createElement("tr", thead);
	var title_length = 6;
	(this.isPopup) && --title_length;
	(this.weekNumbers) && ++title_length;

	hh("?", 1, 400).ttip = Calendar._TT["INFO"];
	this.title = hh("", title_length, 300);
	this.title.className = "title";
	if (this.isPopup) {
		this.title.ttip = Calendar._TT["DRAG_TO_MOVE"];
		this.title.style.cursor = "move";
		hh("&#x00d7;", 1, 200).ttip = Calendar._TT["CLOSE"];
	}

	row = Calendar.createElement("tr", thead);
	row.className = "headrow";

	this._nav_py = hh("&#x00ab;", 1, -2);
	this._nav_py.ttip = Calendar._TT["PREV_YEAR"];

	this._nav_pm = hh("&#x2039;", 1, -1);
	this._nav_pm.ttip = Calendar._TT["PREV_MONTH"];

	this._nav_now = hh(Calendar._TT["TODAY"], this.weekNumbers ? 4 : 3, 0);
	this._nav_now.ttip = Calendar._TT["GO_TODAY"];

	this._nav_nm = hh("&#x203a;", 1, 1);
	this._nav_nm.ttip = Calendar._TT["NEXT_MONTH"];

	this._nav_ny = hh("&#x00bb;", 1, 2);
	this._nav_ny.ttip = Calendar._TT["NEXT_YEAR"];

	// day names
	row = Calendar.createElement("tr", thead);
	row.className = "daynames";
	if (this.weekNumbers) {
		cell = Calendar.createElement("td", row);
		cell.className = "name wn";
		cell.appendChild(document.createTextNode(Calendar._TT["WK"]));
	}
	for (var i = 7; i > 0; --i) {
		cell = Calendar.createElement("td", row);
		cell.appendChild(document.createTextNode(""));
		if (!i) {
			cell.navtype = 100;
			cell.calendar = this;
			Calendar._add_evs(cell);
		}
	}
	this.firstdayname = (this.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;
	this._displayWeekdays();

	var tbody = Calendar.createElement("tbody", table);
	this.tbody = tbody;

	for (i = 6; i > 0; --i) {
		row = Calendar.createElement("tr", tbody);
		if (this.weekNumbers) {
			cell = Calendar.createElement("td", row);
			cell.appendChild(document.createTextNode(""));
		}
		for (var j = 7; j > 0; --j) {
			cell = Calendar.createElement("td", row);
			cell.appendChild(document.createTextNode(""));
			cell.calendar = this;
			Calendar._add_evs(cell);
		}
	}

	if (this.showsTime) {
		row = Calendar.createElement("tr", tbody);
		row.className = "time";

		cell = Calendar.createElement("td", row);
		cell.className = "time";
		cell.colSpan = 2;
		cell.innerHTML = "&nbsp;";

		cell = Calendar.createElement("td", row);
		cell.className = "time";
		cell.colSpan = this.weekNumbers ? 4 : 3;

		(function(){
			function makeTimePart(className, init, range_start, range_end) {
				var part = Calendar.createElement("span", cell);
				part.className = className;
				part.appendChild(document.createTextNode(init));
				part.calendar = cal;
				part.ttip = Calendar._TT["TIME_PART"];
				part.navtype = 50;
				part._range = [];
				if (typeof range_start != "number")
					part._range = range_start;
				else {
					for (var i = range_start; i <= range_end; ++i) {
						var txt;
						if (i < 10 && range_end >= 10) txt = '0' + i;
						else txt = '' + i;
						part._range[part._range.length] = txt;
					}
				}
				Calendar._add_evs(part);
				return part;
			};
			var hrs = cal.date.getHours();
			var mins = cal.date.getMinutes();
			var t12 = !cal.time24;
			var pm = (hrs > 12);
			if (t12 && pm) hrs -= 12;
			var H = makeTimePart("hour", hrs, t12 ? 1 : 0, t12 ? 12 : 23);
			var span = Calendar.createElement("span", cell);
			span.appendChild(document.createTextNode(":"));
			span.className = "colon";
			var M = makeTimePart("minute", mins, 0, 59);
			var AP = null;
			cell = Calendar.createElement("td", row);
			cell.className = "time";
			cell.colSpan = 2;
			if (t12)
				AP = makeTimePart("ampm", pm ? "pm" : "am", ["am", "pm"]);
			else
				cell.innerHTML = "&nbsp;";

			cal.onSetTime = function() {
				var hrs = this.date.getHours();
				var mins = this.date.getMinutes();
				var pm = (hrs > 12);
				if (pm && t12) hrs -= 12;
				H.firstChild.data = (hrs < 10) ? ("0" + hrs) : hrs;
				M.firstChild.data = (mins < 10) ? ("0" + mins) : mins;
				if (t12)
					AP.firstChild.data = pm ? "pm" : "am";
			};

			cal.onUpdateTime = function() {
				var date = this.date;
				var h = parseInt(H.firstChild.data, 10);
				if (t12) {
					if (/pm/i.test(AP.firstChild.data) && h < 12)
						h += 12;
					else if (/am/i.test(AP.firstChild.data) && h == 12)
						h = 0;
				}
				var d = date.getDate();
				var m = date.getMonth();
				var y = date.getFullYear();
				date.setHours(h);
				date.setMinutes(parseInt(M.firstChild.data, 10));
				date.setFullYear(y);
				date.setMonth(m);
				date.setDate(d);
				this.dateClicked = false;
				this.callHandler();
			};
		})();
	} else {
		this.onSetTime = this.onUpdateTime = function() {};
	}

	var tfoot = Calendar.createElement("tfoot", table);

	row = Calendar.createElement("tr", tfoot);
	row.className = "footrow";

	cell = hh(Calendar._TT["SEL_DATE"], this.weekNumbers ? 8 : 7, 300);
	cell.className = "ttip";
	if (this.isPopup) {
		cell.ttip = Calendar._TT["DRAG_TO_MOVE"];
		cell.style.cursor = "move";
	}
	this.tooltips = cell;

	div = Calendar.createElement("div", this.element);
	this.monthsCombo = div;
	div.className = "combo";
	for (i = 0; i < Calendar._MN.length; ++i) {
		var mn = Calendar.createElement("div");
		mn.className = Calendar.is_ie ? "label-IEfix" : "label";
		mn.month = i;
		mn.appendChild(document.createTextNode(Calendar._SMN[i]));
		div.appendChild(mn);
	}

	div = Calendar.createElement("div", this.element);
	this.yearsCombo = div;
	div.className = "combo";
	for (i = 12; i > 0; --i) {
		var yr = Calendar.createElement("div");
		yr.className = Calendar.is_ie ? "label-IEfix" : "label";
		yr.appendChild(document.createTextNode(""));
		div.appendChild(yr);
	}

	this._init(this.mondayFirst, this.date);
	parent.appendChild(this.element);
};

/** keyboard navigation, only for popup calendars */
Calendar._keyEvent = function(ev) {
	if (!window.calendar) {
		return false;
	}
	(Calendar.is_ie) && (ev = window.event);
	var cal = window.calendar;
	var act = (Calendar.is_ie || ev.type == "keypress");
	if (ev.ctrlKey) {
		switch (ev.keyCode) {
		    case 37: // KEY left
			act && Calendar.cellClick(cal._nav_pm);
			break;
		    case 38: // KEY up
			act && Calendar.cellClick(cal._nav_py);
			break;
		    case 39: // KEY right
			act && Calendar.cellClick(cal._nav_nm);
			break;
		    case 40: // KEY down
			act && Calendar.cellClick(cal._nav_ny);
			break;
		    default:
			return false;
		}
	} else switch (ev.keyCode) {
	    case 32: // KEY space (now)
		Calendar.cellClick(cal._nav_now);
		break;
	    case 27: // KEY esc
		act && cal.hide();
		break;
	    case 37: // KEY left
	    case 38: // KEY up
	    case 39: // KEY right
	    case 40: // KEY down
		if (act) {
			var date = cal.date.getDate() - 1;
			var el = cal.currentDateEl;
			var ne = null;
			var prev = (ev.keyCode == 37) || (ev.keyCode == 38);
			switch (ev.keyCode) {
			    case 37: // KEY left
				(--date >= 0) && (ne = cal.ar_days[date]);
				break;
			    case 38: // KEY up
				date -= 7;
				(date >= 0) && (ne = cal.ar_days[date]);
				break;
			    case 39: // KEY right
				(++date < cal.ar_days.length) && (ne = cal.ar_days[date]);
				break;
			    case 40: // KEY down
				date += 7;
				(date < cal.ar_days.length) && (ne = cal.ar_days[date]);
				break;
			}
			if (!ne) {
				if (prev) {
					Calendar.cellClick(cal._nav_pm);
				} else {
					Calendar.cellClick(cal._nav_nm);
				}
				date = (prev) ? cal.date.getMonthDays() : 1;
				el = cal.currentDateEl;
				ne = cal.ar_days[date - 1];
			}
			Calendar.removeClass(el, "selected");
			Calendar.addClass(ne, "selected");
			cal.date.setDate(ne.caldate);
			cal.callHandler();
			cal.currentDateEl = ne;
		}
		break;
	    case 13: // KEY enter
		if (act) {
			cal.callHandler();
			cal.hide();
		}
		break;
	    default:
		return false;
	}
	return Calendar.stopEvent(ev);
};

/**
 *  (RE)Initializes the calendar to the given date and style (if mondayFirst is
 *  true it makes Monday the first day of week, otherwise the weeks start on
 *  Sunday.
 */
Calendar.prototype._init = function (mondayFirst, date) {
	var today = new Date();
	var year = date.getFullYear();
	if (year < this.minYear) {
		year = this.minYear;
		date.setFullYear(year);
	} else if (year > this.maxYear) {
		year = this.maxYear;
		date.setFullYear(year);
	}
	this.mondayFirst = mondayFirst;
	this.date = new Date(date);
	var month = date.getMonth();
	var mday = date.getDate();
	var no_days = date.getMonthDays();
	date.setDate(1);
	var wday = date.getDay();
	var MON = mondayFirst ? 1 : 0;
	var SAT = mondayFirst ? 5 : 6;
	var SUN = mondayFirst ? 6 : 0;
	if (mondayFirst) {
		wday = (wday > 0) ? (wday - 1) : 6;
	}
	var iday = 1;
	var row = this.tbody.firstChild;
	var MN = Calendar._SMN[month];
	var hasToday = ((today.getFullYear() == year) && (today.getMonth() == month));
	var todayDate = today.getDate();
	var week_number = date.getWeekNumber();
	var ar_days = new Array();
	for (var i = 0; i < 6; ++i) {
		if (iday > no_days) {
			row.className = "emptyrow";
			row = row.nextSibling;
			continue;
		}
		var cell = row.firstChild;
		if (this.weekNumbers) {
			cell.className = "day wn";
			cell.firstChild.data = week_number;
			cell = cell.nextSibling;
		}
		++week_number;
		row.className = "daysrow";
		for (var j = 0; j < 7; ++j) {
			cell.className = "day";
			if ((!i && j < wday) || iday > no_days) {
				// cell.className = "emptycell";
				cell.innerHTML = "&nbsp;";
				cell.disabled = true;
				cell = cell.nextSibling;
				continue;
			}
			cell.disabled = false;
			cell.firstChild.data = iday;
			if (typeof this.getDateStatus == "function") {
				date.setDate(iday);
				var status = this.getDateStatus(date, year, month, iday);
				if (status === true) {
					cell.className += " disabled";
					cell.disabled = true;
				} else {
					if (/disabled/i.test(status))
						cell.disabled = true;
					cell.className += " " + status;
				}
			}
			if (!cell.disabled) {
				ar_days[ar_days.length] = cell;
				cell.caldate = iday;
				cell.ttip = "_";
				if (iday == mday) {
					cell.className += " selected";
					this.currentDateEl = cell;
				}
				if (hasToday && (iday == todayDate)) {
					cell.className += " today";
					cell.ttip += Calendar._TT["PART_TODAY"];
				}
				if (wday == SAT || wday == SUN) {
					cell.className += " weekend";
				}
			}
			++iday;
			((++wday) ^ 7) || (wday = 0);
			cell = cell.nextSibling;
		}
		row = row.nextSibling;
	}
	this.ar_days = ar_days;
	this.title.firstChild.data = Calendar._MN[month] + ", " + year;
	this.onSetTime();
	// PROFILE
	// this.tooltips.firstChild.data = "Generated in " + ((new Date()) - today) + " ms";
};

/**
 *  Calls _init function above for going to a certain date (but only if the
 *  date is different than the currently selected one).
 */
Calendar.prototype.setDate = function (date) {
	if (!date.equalsTo(this.date)) {
		this._init(this.mondayFirst, date);
	}
};

/**
 *  Refreshes the calendar.  Useful if the "disabledHandler" function is
 *  dynamic, meaning that the list of disabled date can change at runtime.
 *  Just * call this function if you think that the list of disabled dates
 *  should * change.
 */
Calendar.prototype.refresh = function () {
	this._init(this.mondayFirst, this.date);
};

/** Modifies the "mondayFirst" parameter (EU/US style). */
Calendar.prototype.setMondayFirst = function (mondayFirst) {
	this._init(mondayFirst, this.date);
	this._displayWeekdays();
};

/**
 *  Allows customization of what dates are enabled.  The "unaryFunction"
 *  parameter must be a function object that receives the date (as a JS Date
 *  object) and returns a boolean value.  If the returned value is true then
 *  the passed date will be marked as disabled.
 */
Calendar.prototype.setDateStatusHandler = Calendar.prototype.setDisabledHandler = function (unaryFunction) {
	this.getDateStatus = unaryFunction;
};

/** Customization of allowed year range for the calendar. */
Calendar.prototype.setRange = function (a, z) {
	this.minYear = a;
	this.maxYear = z;
};

/** Calls the first user handler (selectedHandler). */
Calendar.prototype.callHandler = function () {
	if (this.onSelected) {
		this.onSelected(this, this.date.print(this.dateFormat));
	}
};

/** Calls the second user handler (closeHandler). */
Calendar.prototype.callCloseHandler = function () {
	if (this.onClose) {
		this.onClose(this);
	}
	this.hideShowCovered();
};

/** Removes the calendar object from the DOM tree and destroys it. */
Calendar.prototype.destroy = function () {
	var el = this.element.parentNode;
	el.removeChild(this.element);
	Calendar._C = null;
	window.calendar = null;
};

/**
 *  Moves the calendar element to a different section in the DOM tree (changes
 *  its parent).
 */
Calendar.prototype.reparent = function (new_parent) {
	var el = this.element;
	el.parentNode.removeChild(el);
	new_parent.appendChild(el);
};

// This gets called when the user presses a mouse button anywhere in the
// document, if the calendar is shown.  If the click was outside the open
// calendar this function closes it.
Calendar._checkCalendar = function(ev) {
	if (!window.calendar) {
		return false;
	}
	var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
	for (; el != null && el != calendar.element; el = el.parentNode);
	if (el == null) {
		// calls closeHandler which should hide the calendar.
		window.calendar.callCloseHandler();
		return Calendar.stopEvent(ev);
	}
};

/** Shows the calendar. */
Calendar.prototype.show = function () {
	var rows = this.table.getElementsByTagName("tr");
	for (var i = rows.length; i > 0;) {
		var row = rows[--i];
		Calendar.removeClass(row, "rowhilite");
		var cells = row.getElementsByTagName("td");
		for (var j = cells.length; j > 0;) {
			var cell = cells[--j];
			Calendar.removeClass(cell, "hilite");
			Calendar.removeClass(cell, "active");
		}
	}
	this.element.style.display = "block";
	this.hidden = false;
	if (this.isPopup) {
		window.calendar = this;
		Calendar.addEvent(document, "keydown", Calendar._keyEvent);
		Calendar.addEvent(document, "keypress", Calendar._keyEvent);
		Calendar.addEvent(document, "mousedown", Calendar._checkCalendar);
	}
	this.hideShowCovered();
};

/**
 *  Hides the calendar.  Also removes any "hilite" from the class of any TD
 *  element.
 */
Calendar.prototype.hide = function () {
	if (this.isPopup) {
		Calendar.removeEvent(document, "keydown", Calendar._keyEvent);
		Calendar.removeEvent(document, "keypress", Calendar._keyEvent);
		Calendar.removeEvent(document, "mousedown", Calendar._checkCalendar);
	}
	this.element.style.display = "none";
	this.hidden = true;
	this.hideShowCovered();
};

/**
 *  Shows the calendar at a given absolute position (beware that, depending on
 *  the calendar element style -- position property -- this might be relative
 *  to the parent's containing rectangle).
 */
Calendar.prototype.showAt = function (x, y) {
	var s = this.element.style;
	s.left = x + "px";
	s.top = y + "px";
	this.show();
};

/** Shows the calendar near a given element. */
Calendar.prototype.showAtElement = function (el, opts) {
	var self = this;
	var p = Calendar.getAbsolutePos(el);
	if (!opts || typeof opts != "string") {
		this.showAt(p.x, p.y + el.offsetHeight);
		return true;
	}
	this.element.style.display = "block";
	Calendar.continuation_for_the_fucking_khtml_browser = function() {
		var w = self.element.offsetWidth;
		var h = self.element.offsetHeight;
		self.element.style.display = "none";
		var valign = opts.substr(0, 1);
		var halign = "l";
		if (opts.length > 1) {
			halign = opts.substr(1, 1);
		}
		// vertical alignment
		switch (valign) {
		    case "T": p.y -= h; break;
		    case "B": p.y += el.offsetHeight; break;
		    case "C": p.y += (el.offsetHeight - h) / 2; break;
		    case "t": p.y += el.offsetHeight - h; break;
		    case "b": break; // already there
		}
		// horizontal alignment
		switch (halign) {
		    case "L": p.x -= w; break;
		    case "R": p.x += el.offsetWidth; break;
		    case "C": p.x += (el.offsetWidth - w) / 2; break;
		    case "r": p.x += el.offsetWidth - w; break;
		    case "l": break; // already there
		}
		self.showAt(p.x, p.y);
	};
	if (Calendar.is_khtml)
		setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()", 10);
	else
		Calendar.continuation_for_the_fucking_khtml_browser();
};

/** Customizes the date format. */
Calendar.prototype.setDateFormat = function (str) {
	this.dateFormat = str;
};

/** Customizes the tooltip date format. */
Calendar.prototype.setTtDateFormat = function (str) {
	this.ttDateFormat = str;
};

/**
 *  Tries to identify the date represented in a string.  If successful it also
 *  calls this.setDate which moves the calendar to the given date.
 */
Calendar.prototype.parseDate = function (str, fmt) {
	var y = 0;
	var m = -1;
	var d = 0;
	var a = str.split(/\W+/);
	if (!fmt) {
		fmt = this.dateFormat;
	}
	var b = [];
	fmt.replace(/(%.)/g, function(str, par) {
		return b[b.length] = par;
	});
	var i = 0, j = 0;
	var hr = 0;
	var min = 0;
	for (i = 0; i < a.length; ++i) {
		if (b[i] == "%a" || b[i] == "%A") {
			continue;
		}
		if (b[i] == "%d" || b[i] == "%e") {
			d = parseInt(a[i], 10);
		}
		if (b[i] == "%m") {
			m = parseInt(a[i], 10) - 1;
		}
		if (b[i] == "%Y" || b[i] == "%y") {
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
		}
		if (b[i] == "%b" || b[i] == "%B") {
			for (j = 0; j < 12; ++j) {
				if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
			}
		} else if (/%[HIkl]/.test(b[i])) {
			hr = parseInt(a[i], 10);
		} else if (/%[pP]/.test(b[i])) {
			if (/pm/i.test(a[i]) && hr < 12)
				hr += 12;
		} else if (b[i] == "%M") {
			min = parseInt(a[i], 10);
		}
	}
	if (y != 0 && m != -1 && d != 0) {
		this.setDate(new Date(y, m, d, hr, min, 0));
		return;
	}
	y = 0; m = -1; d = 0;
	for (i = 0; i < a.length; ++i) {
		if (a[i].search(/[a-zA-Z]+/) != -1) {
			var t = -1;
			for (j = 0; j < 12; ++j) {
				if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
			}
			if (t != -1) {
				if (m != -1) {
					d = m+1;
				}
				m = t;
			}
		} else if (parseInt(a[i], 10) <= 12 && m == -1) {
			m = a[i]-1;
		} else if (parseInt(a[i], 10) > 31 && y == 0) {
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
		} else if (d == 0) {
			d = a[i];
		}
	}
	if (y == 0) {
		var today = new Date();
		y = today.getFullYear();
	}
	if (m != -1 && d != 0) {
		this.setDate(new Date(y, m, d, hr, min, 0));
	}
};

Calendar.prototype.hideShowCovered = function () {
	var self = this;
	Calendar.continuation_for_the_fucking_khtml_browser = function() {
		function getVisib(obj){
			var value = obj.style.visibility;
			if (!value) {
				if (document.defaultView && typeof (document.defaultView.getComputedStyle) == "function") { // Gecko, W3C
					if (!Calendar.is_khtml)
						value = document.defaultView.
							getComputedStyle(obj, "").getPropertyValue("visibility");
					else
						value = '';
				} else if (obj.currentStyle) { // IE
					value = obj.currentStyle.visibility;
				} else
					value = '';
			}
			return value;
		};

		var tags = new Array("applet", "iframe", "select");
		var el = self.element;

		var p = Calendar.getAbsolutePos(el);
		var EX1 = p.x;
		var EX2 = el.offsetWidth + EX1;
		var EY1 = p.y;
		var EY2 = el.offsetHeight + EY1;

		for (var k = tags.length; k > 0; ) {
			var ar = document.getElementsByTagName(tags[--k]);
			var cc = null;

			for (var i = ar.length; i > 0;) {
				cc = ar[--i];

				p = Calendar.getAbsolutePos(cc);
				var CX1 = p.x;
				var CX2 = cc.offsetWidth + CX1;
				var CY1 = p.y;
				var CY2 = cc.offsetHeight + CY1;

				if (self.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) {
					if (!cc.__msh_save_visibility) {
						cc.__msh_save_visibility = getVisib(cc);
					}
					cc.style.visibility = cc.__msh_save_visibility;
				} else {
					if (!cc.__msh_save_visibility) {
						cc.__msh_save_visibility = getVisib(cc);
					}
					cc.style.visibility = "hidden";
				}
			}
		}
	};
	if (Calendar.is_khtml)
		setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()", 10);
	else
		Calendar.continuation_for_the_fucking_khtml_browser();
};

/** Internal function; it displays the bar with the names of the weekday. */
Calendar.prototype._displayWeekdays = function () {
	var MON = this.mondayFirst ? 0 : 1;
	var SUN = this.mondayFirst ? 6 : 0;
	var SAT = this.mondayFirst ? 5 : 6;
	var cell = this.firstdayname;
	for (var i = 0; i < 7; ++i) {
		cell.className = "day name";
		if (!i) {
			cell.ttip = this.mondayFirst ? Calendar._TT["SUN_FIRST"] : Calendar._TT["MON_FIRST"];
			cell.navtype = 100;
			cell.calendar = this;
			Calendar._add_evs(cell);
		}
		if (i == SUN || i == SAT) {
			Calendar.addClass(cell, "weekend");
		}
		cell.firstChild.data = Calendar._SDN[i + 1 - MON];
		cell = cell.nextSibling;
	}
};

/** Internal function.  Hides all combo boxes that might be displayed. */
Calendar.prototype._hideCombos = function () {
	this.monthsCombo.style.display = "none";
	this.yearsCombo.style.display = "none";
};

/** Internal function.  Starts dragging the element. */
Calendar.prototype._dragStart = function (ev) {
	if (this.dragging) {
		return;
	}
	this.dragging = true;
	var posX;
	var posY;
	if (Calendar.is_ie) {
		posY = window.event.clientY + document.body.scrollTop;
		posX = window.event.clientX + document.body.scrollLeft;
	} else {
		posY = ev.clientY + window.scrollY;
		posX = ev.clientX + window.scrollX;
	}
	var st = this.element.style;
	this.xOffs = posX - parseInt(st.left);
	this.yOffs = posY - parseInt(st.top);
	with (Calendar) {
		addEvent(document, "mousemove", calDragIt);
		addEvent(document, "mouseover", stopEvent);
		addEvent(document, "mouseup", calDragEnd);
	}
};

// BEGIN: DATE OBJECT PATCHES

/** Adds the number of days array to the Date object. */
Date._MD = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

/** Constants used for time computations */
Date.SECOND = 1000 /* milliseconds */;
Date.MINUTE = 60 * Date.SECOND;
Date.HOUR   = 60 * Date.MINUTE;
Date.DAY    = 24 * Date.HOUR;
Date.WEEK   =  7 * Date.DAY;

/** Returns the number of days in the current month */
Date.prototype.getMonthDays = function(month) {
	var year = this.getFullYear();
	if (typeof month == "undefined") {
		month = this.getMonth();
	}
	if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
		return 29;
	} else {
		return Date._MD[month];
	}
};

/** Returns the number of day in the year. */
Date.prototype.getDayOfYear = function() {
	var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var then = new Date(this.getFullYear(), 0, 1, 0, 0, 0);
	var time = now - then;
	return Math.floor(time / Date.DAY);
};

/** Returns the number of the week in year, as defined in ISO 8601. */
Date.prototype.getWeekNumber = function() {
	var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var then = new Date(this.getFullYear(), 0, 1, 0, 0, 0);
	var time = now - then;
	var day = then.getDay(); // 0 means Sunday
	if (day == 0) day = 7;
	(day > 4) && (day -= 4) || (day += 3);
	return Math.round(((time / Date.DAY) + day) / 7);
};

/** Checks dates equality (ignores time) */
Date.prototype.equalsTo = function(date) {
	return ((this.getFullYear() == date.getFullYear()) &&
		(this.getMonth() == date.getMonth()) &&
		(this.getDate() == date.getDate()) &&
		(this.getHours() == date.getHours()) &&
		(this.getMinutes() == date.getMinutes()));
};

/** Prints the date in a string according to the given format. */
Date.prototype.print = function (str) {
	var m = this.getMonth();
	var d = this.getDate();
	var y = this.getFullYear();
	var wn = this.getWeekNumber();
	var w = this.getDay();
	var s = {};
	var hr = this.getHours();
	var pm = (hr >= 12);
	var ir = (pm) ? (hr - 12) : hr;
	var dy = this.getDayOfYear();
	if (ir == 0)
		ir = 12;
	var min = this.getMinutes();
	var sec = this.getSeconds();
	s["%a"] = Calendar._SDN[w]; // abbreviated weekday name [FIXME: I18N]
	s["%A"] = Calendar._DN[w]; // full weekday name
	s["%b"] = Calendar._SMN[m]; // abbreviated month name [FIXME: I18N]
	s["%B"] = Calendar._MN[m]; // full month name
	// FIXME: %c : preferred date and time representation for the current locale
	s["%C"] = 1 + Math.floor(y / 100); // the century number
	s["%d"] = (d < 10) ? ("0" + d) : d; // the day of the month (range 01 to 31)
	s["%e"] = d; // the day of the month (range 1 to 31)
	// FIXME: %D : american date style: %m/%d/%y
	// FIXME: %E, %F, %G, %g, %h (man strftime)
	s["%H"] = (hr < 10) ? ("0" + hr) : hr; // hour, range 00 to 23 (24h format)
	s["%I"] = (ir < 10) ? ("0" + ir) : ir; // hour, range 01 to 12 (12h format)
	s["%j"] = (dy < 100) ? ((dy < 10) ? ("00" + dy) : ("0" + dy)) : dy; // day of the year (range 001 to 366)
	s["%k"] = hr;		// hour, range 0 to 23 (24h format)
	s["%l"] = ir;		// hour, range 1 to 12 (12h format)
	s["%m"] = (m < 9) ? ("0" + (1+m)) : (1+m); // month, range 01 to 12
	s["%M"] = (min < 10) ? ("0" + min) : min; // minute, range 00 to 59
	s["%n"] = "\n";		// a newline character
	s["%p"] = pm ? "PM" : "AM";
	s["%P"] = pm ? "pm" : "am";
	// FIXME: %r : the time in am/pm notation %I:%M:%S %p
	// FIXME: %R : the time in 24-hour notation %H:%M
	s["%s"] = Math.floor(this.getTime() / 1000);
	s["%S"] = (sec < 10) ? ("0" + sec) : sec; // seconds, range 00 to 59
	s["%t"] = "\t";		// a tab character
	// FIXME: %T : the time in 24-hour notation (%H:%M:%S)
	s["%U"] = s["%W"] = s["%V"] = (wn < 10) ? ("0" + wn) : wn;
	s["%u"] = w + 1;	// the day of the week (range 1 to 7, 1 = MON)
	s["%w"] = w;		// the day of the week (range 0 to 6, 0 = SUN)
	// FIXME: %x : preferred date representation for the current locale without the time
	// FIXME: %X : preferred time representation for the current locale without the date
	s["%y"] = ('' + y).substr(2, 2); // year without the century (range 00 to 99)
	s["%Y"] = y;		// year with the century
	s["%%"] = "%";		// a literal '%' character
	var re = Date._msh_formatRegexp;
	if (typeof re == "undefined") {
		var tmp = "";
		for (var i in s)
			tmp += tmp ? ("|" + i) : i;
		Date._msh_formatRegexp = re = new RegExp("(" + tmp + ")", 'g');
	}
	return str.replace(re, function(match, par) { return s[par]; });
};

// END: DATE OBJECT PATCHES

// global object that remembers the calendar
window.calendar = null;


/**
 *  This function "patches" an input field (or other element) to use a calendar
 *  widget for date selection.
 *
 *  The "params" is a single object that can have the following properties:
 *
 *    prop. name   | description
 *  -------------------------------------------------------------------------------------------------
 *   inputField    | the ID of an input field to store the date
 *   displayArea   | the ID of a DIV or other element to show the date
 *   button        | ID of a button or other element that will trigger the calendar
 *   eventName     | event that will trigger the calendar, without the "on" prefix (default: "click")
 *   ifFormat      | date format that will be stored in the input field
 *   daFormat      | the date format that will be used to display the date in displayArea
 *   singleClick   | (true/false) wether the calendar is in single click mode or not (default: true)
 *   mondayFirst   | (true/false) if true Monday is the first day of week, Sunday otherwise (default: true)
 *   align         | alignment (default: "Bl"); if you don't know what's this see the calendar documentation
 *   range         | array with 2 elements.  Default: [1900, 2999] -- the range of years available
 *   weekNumbers   | (true/false) if it's true (default) the calendar will display week numbers
 *   flat          | null or element ID; if not null the calendar will be a flat calendar having the parent with the given ID
 *   flatCallback  | function that receives a JS Date object and returns an URL to point the browser to (for flat calendar)
 *   disableFunc   | function that receives a JS Date object and should return true if that date has to be disabled in the calendar
 *   onSelect      | function that gets called when a date is selected.  You don't _have_ to supply this (the default is generally okay)
 *   onClose       | function that gets called when the calendar is closed.  [default]
 *   onUpdate      | function that gets called after the date is modified in the input field.  Receives a reference to the calendar.
 *   date          | the date that the calendar will be initially displayed to
 *   showsTime     | default: false; if true the calendar will include a time selector
 *   timeFormat    | the time format; can be "12" or "24", default is "12"
 *
 *  None of them is required, they all have default values.  However, if you
 *  pass none of "inputField", "displayArea" or "button" you'll get a warning
 *  saying "nothing to setup".
 */
Calendar.setup = function (params) {
	function param_default(pname, def) { if (typeof params[pname] == "undefined") { params[pname] = def; } };

	param_default("inputField",     null);
	param_default("displayArea",    null);
	param_default("button",         null);
	param_default("eventName",      "click");
	param_default("ifFormat",       "%Y-%m-%d");
	param_default("daFormat",       "%Y-%m-%d");
	param_default("singleClick",    true);
	param_default("disableFunc",    null);
	param_default("dateStatusFunc", params["disableFunc"]);	// takes precedence if both are defined
	param_default("mondayFirst",    true);
	param_default("align",          "Bl");
	param_default("range",          [1900, 2999]);
	param_default("weekNumbers",    true);
	param_default("flat",           null);
	param_default("flatCallback",   null);
	param_default("onSelect",       null);
	param_default("onClose",        null);
	param_default("onUpdate",       null);
	param_default("date",           null);
	param_default("showsTime",      false);
	param_default("timeFormat",     "24");

	var tmp = ["inputField", "displayArea", "button"];
	for (var i in tmp) {
		if (typeof params[tmp[i]] == "string") {
			params[tmp[i]] = document.getElementById(params[tmp[i]]);
		}
	}
	if (!(params.flat || params.inputField || params.displayArea || params.button)) {
		alert("Calendar.setup:\n  Nothing to setup (no fields found).  Please check your code");
		return false;
	}

	function onSelect(cal) {
		if (cal.params.flat) {
			if (typeof cal.params.flatCallback == "function") {
				cal.params.flatCallback(cal);
			} else {
				alert("No flatCallback given -- doing nothing.");
			}
			return false;
		}
		if (cal.params.inputField) {
			cal.params.inputField.value = cal.date.print(cal.params.ifFormat);
		}
		if (cal.params.displayArea) {
			cal.params.displayArea.innerHTML = cal.date.print(cal.params.daFormat);
		}
		if (cal.params.singleClick && cal.dateClicked) {
			cal.callCloseHandler();
		}
		if (typeof cal.params.onUpdate == "function") {
			cal.params.onUpdate(cal);
		}
	};

	if (params.flat != null) {
		params.flat = document.getElementById(params.flat);
		if (!params.flat) {
			alert("Calendar.setup:\n  Flat specified but can't find parent.");
			return false;
		}
		var cal = new Calendar(params.mondayFirst, params.date, params.onSelect || onSelect);
		cal.showsTime = params.showsTime;
		cal.time24 = (params.timeFormat == "24");
		cal.params = params;
		cal.weekNumbers = params.weekNumbers;
		cal.setRange(params.range[0], params.range[1]);
		cal.setDateStatusHandler(params.dateStatusFunc);
		cal.create(params.flat);
		cal.show();
		return false;
	}

	var triggerEl = params.button || params.displayArea || params.inputField;
	triggerEl["on" + params.eventName] = function() {
		var dateEl = params.inputField || params.displayArea;
		var dateFmt = params.inputField ? params.ifFormat : params.daFormat;
		var mustCreate = false;
		var cal = window.calendar;
		if (!window.calendar) {
			window.calendar = cal = new Calendar(params.mondayFirst,
							     params.date,
							     params.onSelect || onSelect,
							     params.onClose || function(cal) { cal.hide(); });
			cal.showsTime = params.showsTime;
			cal.time24 = (params.timeFormat == "24");
			cal.weekNumbers = params.weekNumbers;
			mustCreate = true;
		} else {
			cal.hide();
		}
		cal.setRange(params.range[0], params.range[1]);
		cal.params = params;
		cal.setDateStatusHandler(params.dateStatusFunc);
		cal.setDateFormat(dateFmt);
		if (mustCreate)
			cal.create();
		cal.parseDate(dateEl.value || dateEl.innerHTML);
		cal.refresh();
		cal.showAtElement(params.displayArea || params.inputField, params.align);
		return false;
	};
};


// full day names
Calendar._DN = new Array
("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

// short day names
Calendar._SDN = new Array
("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun");

// full month names
Calendar._MN = new Array
("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

// short month names
Calendar._SMN = new Array
("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "About the calendar";

Calendar._TT["ABOUT"] =
"Date selection:\n" +
"- Use the \xab, \xbb buttons to select year\n" +
"- Use the " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons to select month\n" +
"- Hold mouse button on any of the above buttons for faster selection.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Time selection:\n" +
"- Click on any of the time parts to increase it\n" +
"- or Shift-click to decrease it\n" +
"- or click and drag for faster selection.";

Calendar._TT["PREV_YEAR"] = "Prev. year (hold for menu)";
Calendar._TT["PREV_MONTH"] = "Prev. month (hold for menu)";
Calendar._TT["GO_TODAY"] = "Go Today";
Calendar._TT["NEXT_MONTH"] = "Next month (hold for menu)";
Calendar._TT["NEXT_YEAR"] = "Next year (hold for menu)";
Calendar._TT["SEL_DATE"] = "Select date";
Calendar._TT["DRAG_TO_MOVE"] = "Drag to move";
Calendar._TT["PART_TODAY"] = " (today)";
Calendar._TT["MON_FIRST"] = "Display Monday first";
Calendar._TT["SUN_FIRST"] = "Display Sunday first";
Calendar._TT["CLOSE"] = "Close";
Calendar._TT["TODAY"] = "Today";
Calendar._TT["TIME_PART"] = "(Shift-)Click or drag to change value";
// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";
Calendar._TT["WK"] = "wk";










//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************

// Determine browser and version.

function formBrowser() {

  var ua, s, i;

  this.isIE    = false;  // Internet Explorer
  this.isNS    = false;  // Netscape
  this.version = null;

  ua = navigator.userAgent;

  s = "MSIE";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  // Treat any other "Gecko" browser as NS 6.1.

  s = "Gecko";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }
}

var mybrowser = new formBrowser();

//=============================================================================
// Window Object
//=============================================================================

function Window(el) {

  var i, mapList, mapName;

  // Get window components.
  this.frame           = el;
  this.titleBar        = winFindByClassName(el, "titleBarText");
  this.titleBarText    = winFindByClassName(el, "titleBarText");
  this.titleBarButtons = winFindByClassName(el, "titleBarButtonsRow");
  this.clientArea      = winFindByClassName(el, "clientArea");

  // Set flags.

  this.isOpen      = false;
  this.isMinimized = false;

  // Set methods.

  this.open       = winOpen;
  this.close      = winClose;
  this.minimize   = winMinimize;
  this.restore    = winRestore;
  this.makeActive = winMakeActive;

  // Set up event handling.

  this.frame.parentWindow = this;
  //open this comment to make resizable form
  //-------------------------------------------------------------
  //this.frame.onmousemove  = winResizeCursorSet;
  //this.frame.onmouseout   = winResizeCursorRestore;
  //this.frame.onmousedown  = winResizeDragStart;

  this.titleBar.parentWindow = this;
  this.titleBar.onmousedown  = winMoveDragStart;

  this.clientArea.parentWindow = this;
  this.clientArea.onclick      = winClientAreaClick;

//  idx = 0;
  for (i = 0; i < this.titleBarButtons.childNodes.length; i++)
    if (this.titleBarButtons.childNodes[i].tagName == "IMG")
    {
  //    idx++;
      this.titleBarButtons.childNodes[i].parentWindow = this;
      /*if (idx==1)
        this.titleBarButtons.childNodes[i].onclick=this.minimize;
      else if (idx==2)
        this.titleBarButtons.childNodes[i].onclick=this.restore;
      else
        this.titleBarButtons.childNodes[i].onclick=this.close;*/
    }
  // Calculate the minimum width and height values for resizing
  // and fix any initial display problems.

  var initLt, initWd, w, dw;

  // Save the inital frame width and position, then reposition
  // the window.
  
  initLt = this.frame.style.left;
  initWd = parseInt(this.frame.style.width);
  this.frame.style.left = -this.titleBarText.offsetWidth + "px";

  // For IE, start calculating the value to use when setting
  // the client area width based on the frame width.

  if (mybrowser.isIE) 
  {
    this.titleBarText.style.display = "none";
    w = this.clientArea.offsetWidth;
    this.widthDiff = this.frame.offsetWidth - w;
    this.clientArea.style.width = w + "px";
    dw = this.clientArea.offsetWidth - w;
    w -= dw;     
    this.widthDiff += dw;
    this.titleBarText.style.display = "";
  }

  // Find the difference between the frame's style and offset
  // widths. For IE, adjust the client area/frame width
  // difference accordingly.

  w = this.frame.offsetWidth;
  this.frame.style.width = w + "px";
  dw = this.frame.offsetWidth - w;
  w -= dw;     
  this.frame.style.width = w + "px";
  if (mybrowser.isIE)
    this.widthDiff -= dw;

  // Find the minimum width for resize.

  this.isOpen = true;  // Flag as open so minimize call will work.
  this.minimize();
  // Get the minimum width.
  if (mybrowser.isNS && mybrowser.version >= 1.2)
    // For later versions of Gecko.
    this.minimumWidth = this.frame.offsetWidth;
  else
    // For all others.
    this.minimumWidth = this.frame.offsetWidth - dw;
  // Find the frame width at which or below the title bar text will
  // need to be clipped.

 
  this.titleBarText.style.width = "";
  this.clipTextMinimumWidth = this.frame.offsetWidth - dw;

  // Set the minimum height.

  this.minimumHeight = 50;

  // Restore window. For IE, set client area width.

  this.restore();
  this.isOpen = false;  // Reset flag.
  initWd = Math.max(initWd, this.minimumWidth);
  this.frame.style.width = initWd + "px";
  if (mybrowser.isIE)
    this.clientArea.style.width = (initWd - this.widthDiff) + "px";

  // Clip the title bar text if needed.

  if (this.clipTextMinimumWidth >= this.minimumWidth)
    this.titleBarText.style.width = (winCtrl.minimizedTextWidth + initWd - this.minimumWidth) + "px";

  // Restore the window to its original position.

  this.frame.style.left = initLt;
}

//=============================================================================
// Window Methods
//=============================================================================

function winOpen() {

  if (this.isOpen)
    return;

  // Restore the window and make it visible.

  this.makeActive();
  this.isOpen = true;
  if (this.isMinimized)
    this.restore();
  this.frame.style.visibility = "visible";
  (document.all) ? this.frame.style.display = "block" : this.frame.style.display = "table-row";
}

function winClose() {

  // Hide the window.
  this.frame.style.visibility = "hidden";
  this.frame.style.display = "none";
  this.isOpen = false;
}

function winMinimize() 
{

  if (!this.isOpen || this.isMinimized)
    return;

  this.makeActive();

  // Save current frame and title bar text widths.

  this.restoreFrameWidth = this.frame.style.width;
  this.restoreTextWidth = this.titleBarText.style.width;

  // Disable client area display.

  this.clientArea.style.display = "none";
  
  // Minimize frame and title bar text widths.
  //alert(winCtrl.minimizedTextWidth );
  if (this.minimumWidth)
    this.frame.style.width = this.minimumWidth + "px";
  else
    this.frame.style.width = "";
  this.titleBarText.style.width = (winCtrl.minimizedTextWidth + 100) + "px";

  this.isMinimized = true;
}

function winRestore() {

  if (!this.isOpen || !this.isMinimized)
    return;

  this.makeActive();

  // Enable client area display.

  this.clientArea.style.display = "";

  // Restore frame and title bar text widths.

  this.frame.style.width = this.restoreFrameWidth;
  this.titleBarText.style.width = this.restoreTextWidth;

  this.isMinimized = false;
}

function winMakeActive() {

  if (winCtrl.active == this)
    return;

  // Inactivate the currently active window.

  if (winCtrl.active) {
    //winCtrl.active.frame.style.backgroundColor    = winCtrl.inactiveFrameBackgroundColor;
    //winCtrl.active.frame.style.borderColor        = winCtrl.inactiveFrameBorderColor;
    //winCtrl.active.titleBar.style.backgroundColor = winCtrl.inactiveTitleBarColor;
    //winCtrl.active.titleBar.style.color           = winCtrl.inactiveTitleTextColor;
    //winCtrl.active.clientArea.style.borderColor   = winCtrl.inactiveClientAreaBorderColor;
    //if (mybrowser.isIE)
    //  winCtrl.active.clientArea.style.scrollbarBaseColor = winCtrl.inactiveClientAreaScrollbarColor;
    if (mybrowser.isNS && mybrowser.version < 6.1)
      winCtrl.active.clientArea.style.overflow = "hidden";
    //if (winCtrl.active.inactiveButtonsImage)
    //  winCtrl.active.titleBarButtons.src = winCtrl.active.inactiveButtonsImage;
  }

  // Activate this window.

  //this.frame.style.backgroundColor    = this.activeFrameBackgroundColor;
  //this.frame.style.borderColor        = this.activeFrameBorderColor;
  //this.titleBar.style.backgroundColor = this.activeTitleBarColor;
  //this.titleBar.style.color           = this.activeTitleTextColor;
  //this.clientArea.style.borderColor   = this.activeClientAreaBorderColor;
  //if (mybrowser.isIE)
  //  this.clientArea.style.scrollbarBaseColor = this.activeClientAreaScrollbarColor;
  if (mybrowser.isNS && mybrowser.version < 6.1)
    this.clientArea.style.overflow = "auto";
  //if (this.inactiveButtonsImage)
  //  this.titleBarButtons.src = this.activeButtonsImage;
  this.frame.style.zIndex = ++winCtrl.maxzIndex;
  winCtrl.active = this;
}

//=============================================================================
// Event handlers.
//=============================================================================

function winClientAreaClick(event) {

  // Make this window the active one.

  this.parentWindow.makeActive();
}

//-----------------------------------------------------------------------------
// Window dragging.
//-----------------------------------------------------------------------------

function winMoveDragStart(event) {

  var target;
  var x, y;

  if (mybrowser.isIE)
    target = window.event.srcElement.tagName;
  if (mybrowser.isNS)
    target = event.target.tagName;

  if (target == "AREA")
    return;

  this.parentWindow.makeActive();

  // Get cursor offset from window frame.

  if (mybrowser.isIE) {
    x = window.event.x;
    y = window.event.y;
  }
  if (mybrowser.isNS) {
    x = event.pageX;
    y = event.pageY;
  }
  winCtrl.xOffset = winCtrl.active.frame.offsetLeft - x;
  winCtrl.yOffset = winCtrl.active.frame.offsetTop  - y;

  // Set document to capture mousemove and mouseup events.

  if (mybrowser.isIE) {
    document.onmousemove = winMoveDragGo;
    document.onmouseup   = winMoveDragStop;
  }
  if (mybrowser.isNS) {
    document.addEventListener("mousemove", winMoveDragGo,   true);
    document.addEventListener("mouseup",   winMoveDragStop, true);
    event.preventDefault();
  }

  winCtrl.inMoveDrag = true;
}

function winMoveDragGo(event) {

  var x, y;

  if (!winCtrl.inMoveDrag)
    return;

  // Get cursor position.

  if (mybrowser.isIE) {
    x = window.event.x;
    y = window.event.y;
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (mybrowser.isNS) {
    x = event.pageX;
    y = event.pageY;
    event.preventDefault();
  }

  // Move window frame based on offset from cursor.

  winCtrl.active.frame.style.left = (x + winCtrl.xOffset) + "px";
  winCtrl.active.frame.style.top  = (y + winCtrl.yOffset) + "px";
}

function winMoveDragStop(event) {

  winCtrl.inMoveDrag = false;

  // Remove mousemove and mouseup event captures on document.

  if (mybrowser.isIE) {
    document.onmousemove = null;
    document.onmouseup   = null;
  }
  if (mybrowser.isNS) {
    document.removeEventListener("mousemove", winMoveDragGo,   true);
    document.removeEventListener("mouseup",   winMoveDragStop, true);
  }
}

//-----------------------------------------------------------------------------
// Window resizing.
//-----------------------------------------------------------------------------

function winResizeCursorSet(event) {

  var target;
  var xOff, yOff;

  if (this.parentWindow.isMinimized || winCtrl.inResizeDrag)
    return;

  // If not on window frame, restore cursor and exit.

  if (mybrowser.isIE)
    target = window.event.srcElement;
  if (mybrowser.isNS)
    target = event.target;
  if (target != this.parentWindow.frame)
    return;

  // Find resize direction.

  if (mybrowser.isIE) {
    xOff = window.event.offsetX;
    yOff = window.event.offsetY;
  }
  if (mybrowser.isNS) {
    xOff = event.layerX;
    yOff = event.layerY;
  }
  winCtrl.resizeDirection = ""
  if (yOff <= winCtrl.resizeCornerSize)
    winCtrl.resizeDirection += "n";
  else if (yOff >= this.parentWindow.frame.offsetHeight - winCtrl.resizeCornerSize)
    winCtrl.resizeDirection += "s";
  if (xOff <= winCtrl.resizeCornerSize)
    winCtrl.resizeDirection += "w";
  else if (xOff >= this.parentWindow.frame.offsetWidth - winCtrl.resizeCornerSize)
    winCtrl.resizeDirection += "e";

  // If not on window edge, restore cursor and exit.

  if (winCtrl.resizeDirection == "") {
    this.onmouseout(event);
    return;
  }
  //alert(yOff+","+this.parentWindow.frame.offsetHeight );

  // Change cursor.

  if (mybrowser.isIE)
    document.body.style.cursor = winCtrl.resizeDirection + "-resize";
  if (mybrowser.isNS)
    this.parentWindow.frame.style.cursor = winCtrl.resizeDirection + "-resize";
}

function winResizeCursorRestore(event) {

  if (winCtrl.inResizeDrag)
    return;

  // Restore cursor.

  if (mybrowser.isIE)
    document.body.style.cursor = "";
  if (mybrowser.isNS)
    this.parentWindow.frame.style.cursor = "";
}

function winResizeDragStart(event) {

  var target;

  // Make sure the event is on the window frame.

  if (mybrowser.isIE)
    target = window.event.srcElement;
  if (mybrowser.isNS)
    target = event.target;
  if (target != this.parentWindow.frame)
    return;

  this.parentWindow.makeActive();

  if (this.parentWindow.isMinimized)
    return;

  // Save cursor position.

  if (mybrowser.isIE) {
    winCtrl.xPosition = window.event.x;
    winCtrl.yPosition = window.event.y;
  }
  if (mybrowser.isNS) {
    winCtrl.xPosition = event.pageX;
    winCtrl.yPosition = event.pageY;
  }

  // Save window frame position and current window size.

  winCtrl.oldLeft   = parseInt(this.parentWindow.frame.style.left,  10);
  winCtrl.oldTop    = parseInt(this.parentWindow.frame.style.top,   10);
  winCtrl.oldWidth  = parseInt(this.parentWindow.frame.style.width, 10);
  winCtrl.oldHeight = parseInt(this.parentWindow.clientArea.style.height, 10);

  // Set document to capture mousemove and mouseup events.

  if (mybrowser.isIE) {
    document.onmousemove = winResizeDragGo;
    document.onmouseup   = winResizeDragStop;
  }
  if (mybrowser.isNS) {
    document.addEventListener("mousemove", winResizeDragGo,   true);
    document.addEventListener("mouseup"  , winResizeDragStop, true);
    event.preventDefault();
  }

  winCtrl.inResizeDrag = true;
}

function winResizeDragGo(event) {

 var north, south, east, west;
 var dx, dy;
 var w, h;

  if (!winCtrl.inResizeDrag)
    return;

  // Set direction flags based on original resize direction.

  north = false;
  south = false;
  east  = false;
  west  = false;
  if (winCtrl.resizeDirection.charAt(0) == "n")
    north = true;
  if (winCtrl.resizeDirection.charAt(0) == "s")
    south = true;
  if (winCtrl.resizeDirection.charAt(0) == "e" || winCtrl.resizeDirection.charAt(1) == "e")
    east = true;
  if (winCtrl.resizeDirection.charAt(0) == "w" || winCtrl.resizeDirection.charAt(1) == "w")
    west = true;

  // Find change in cursor position.

  if (mybrowser.isIE) {
    dx = window.event.x - winCtrl.xPosition;
    dy = window.event.y - winCtrl.yPosition;
  }
  if (mybrowser.isNS) {
    dx = event.pageX - winCtrl.xPosition;
    dy = event.pageY - winCtrl.yPosition;
  }

  // If resizing north or west, reverse corresponding amount.

  if (west)
    dx = -dx;
  if (north)
    dy = -dy;

  // Check new size.

  w = winCtrl.oldWidth  + dx;
  h = winCtrl.oldHeight + dy;
  if (w <= winCtrl.active.minimumWidth) {
    w = winCtrl.active.minimumWidth;
    dx = w - winCtrl.oldWidth;
  }
  if (h <= winCtrl.active.minimumHeight) {
    h = winCtrl.active.minimumHeight;
    dy = h - winCtrl.oldHeight;
  }

  // Resize the window. For IE, keep client area and frame widths in synch.

  if (east || west) {
    winCtrl.active.frame.style.width = w + "px";
    if (mybrowser.isIE)
      winCtrl.active.clientArea.style.width = (w - winCtrl.active.widthDiff) + "px";
  }
  if (north || south)
    winCtrl.active.clientArea.style.height = h + "px";

  // Clip the title bar text, if necessary.

  if (east || west) {
    if (w < winCtrl.active.clipTextMinimumWidth)
      winCtrl.active.titleBarText.style.width = (winCtrl.minimizedTextWidth + w - winCtrl.active.minimumWidth) + "px";
    else
      winCtrl.active.titleBarText.style.width = "";
  }
//alert(winCtrl.active.titleBarText.style.width +","+w+","+ winCtrl.minimizedTextWidth+","+ winCtrl.active.minimumWidth);
  // For a north or west resize, move the window.

  if (west)
    winCtrl.active.frame.style.left = (winCtrl.oldLeft - dx) + "px";
  if (north)
    winCtrl.active.frame.style.top  = (winCtrl.oldTop  - dy) + "px";

  if (mybrowser.isIE) {
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (mybrowser.isNS)
    event.preventDefault();
}

function winResizeDragStop(event) {

  winCtrl.inResizeDrag = false;

  // Remove mousemove and mouseup event captures on document.

  if (mybrowser.isIE) {
    document.onmousemove = null;
    document.onmouseup   = null;
  }
  if (mybrowser.isNS) {
    document.removeEventListener("mousemove", winResizeDragGo,   true);
    document.removeEventListener("mouseup"  , winResizeDragStop, true);
  }
}

//=============================================================================
// Utility functions.
//=============================================================================

function winFindByClassName(el, className) {

  var i, tmp;

  if (el.className == className)
    return el;

  // Search for a descendant element assigned the given class.

  for (i = 0; i < el.childNodes.length; i++) {
    tmp = winFindByClassName(el.childNodes[i], className);
    if (tmp != null)
      return tmp;
  }

  return null;
}

//=============================================================================
// Initialization code.
//=============================================================================

var winList = new Array();
var winCtrl = new Object();

function winInit() {

  var elList;

  // Initialize window control object.

  winCtrl.maxzIndex                        =   0;
  winCtrl.resizeCornerSize                 =  15;
  winCtrl.minimizedTextWidth               = 100;
/*  winCtrl.inactiveFrameBackgroundColor     = "#c0c0c0";
  winCtrl.inactiveFrameBorderColor         = "#f0f0f0 #505050 #404040 #e0e0e0";
  winCtrl.inactiveTitleBarColor            = "#808080";
  winCtrl.inactiveTitleTextColor           = "#c0c0c0";
  winCtrl.inactiveClientAreaBorderColor    = "#404040 #e0e0e0 #f0f0f0 #505050";
  winCtrl.inactiveClientAreaScrollbarColor = "";
 */ winCtrl.inMoveDrag                       = false;
  winCtrl.inResizeDrag                     = false;

  // Initialize windows and build list.

  elList = document.getElementsByTagName("DIV");
  for (var i = 0; i < elList.length; i++)
    if (elList[i].className == "window")
      winList[elList[i].id] = new Window(elList[i]);
}