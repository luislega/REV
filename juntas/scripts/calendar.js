// JavaScript Document
function makeWidget (opts) {
	var available_langs = new Array ("espanol", "english", "francais");
	var defaultCallback = function () {
		alert ($(this).data("date").toSQLDateString());
	}
	
	Date.prototype.daysInMonth = function () {
		return (new Date (this.getFullYear(), this.getMonth()+1, 0).getDate());
	}
	
	Date.prototype.toSQLDateString = String.prototype.toSQLDateString = function () {
		var date = this.constructor === String?new Date(this):this;
		return (date.getFullYear()+"-"+put0s(date.getMonth()+1)+"-"+put0s(date.getDate()));
	}
	
	Date.prototype.toReadableDateString = String.prototype.toReadableDateString = function () {
		var date = this.constructor === String?new Date(this):this;
		return put0s(date.getDate()) + "/" + put0s(date.getMonth()+1) + "/" + date.getFullYear();
	}
	
	Date.prototype.mes = function (lang) {
		var lang = lang || "espanol";
		var months_espanol = new Array ("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
		var months_english = new Array ("January","February","March","April","May","June","July","August","September","October","November","December");
		var months_francais = new Array ("janvier","février","mars","avril","mai","juin","julliet","août","septambre","octobre","novembre","décembre");
		return (eval("months_"+lang)[this.getMonth()]);
	}
	
	Date.prototype.firstOfMonth = function () {
		return (new Date(this.getFullYear(), this.getMonth(), 1)).getDay();
	}
	
	var type = typeof (opts), month, lang, after, callback;
	
	switch (type) {
		case "object":
			month = isDate(opts)?isDate(opts):new Date();
			lang = isLang(opts)?opts:"espanol";
			after = isElement(opts);
			callback = opts.callback?isFunction(opts.callback)?eval(opts.callback):defaultCallback:defaultCallback;
			
			break;
		case "string":
			month = isDate(opts)?isDate(opts):new Date();
			lang = isLang(opts)?opts:"espanol";
			after = isElement(opts);
			callback = isFunction(opts)?eval(opts):defaultCallback;
			
			break;
		case "function":
			month = new Date();
			lang = "espanol";
			after = null;
			callback = opts;
			
			break;
		default:
			month = new Date();
			lang = "espanol";
			after = null;
			callback = defaultCallback;
			
			break;
	}
	//$('#calendar').html(month+"<br>"+lang+"<br>"+after+"<br>"+callback);
	
	var drawnMonth = month;
	var today = new Date();
	
	var weekHeads = makeHeads ();
	
	var calendar = $(document.createElement("p"));
	
	var daysDiv = $(document.createElement("p"));
	var calendarUL = $(document.createElement("ul"));
	calendar.addClass ("calendar");
	var prev = $('<p id="prev" class="calendarHead clickable">&lt;</p>');
	var next = $('<p id="next" class="calendarHead clickable">&gt;</p>');
	calendar.append(prev);
	calendar.append('<p id="monthName" class="calendarHead" style="width:80%">'+month.mes(lang)+'</p>');
	calendar.append(next);
	calendar.append(daysDiv);
	daysDiv.append(calendarUL);
	daysDiv.attr('id', 'daysDiv');
	var dayInitials = makeHeads(lang);
	
	/*if (after) {
		after.after(calendar);
	}else {
		$('body').append(calendar)
	}*/
	
	var builtMonths = {};
	
	calendar.drawDays = function (m) {
		var m = m?typeof(m)=="date"?m:new Date(m):new Date();
		calendarUL.html(dayInitials);
		$('#monthName').html(m.mes(lang));
		//var start = new Date().getMilliseconds();
		//$('#calendar').html (m.getFullYear()+"-"+m.getMonth())
		if (!builtMonths[m.getFullYear()+"-"+m.getMonth()]) {
			var firstOfMonth = m.firstOfMonth();
			var daysN = m.daysInMonth ();
			var limit = Math.ceil((daysN+firstOfMonth)/7)*7;
			var days = new Array();
			for (var i = -firstOfMonth; i < limit-firstOfMonth; i++) {
				var day = $(document.createElement('li'));
				var dayToDraw = new Date (m.getFullYear(), m.getMonth(), i+1);
				day.html(dayToDraw.getDate());
				day.addClass("calendarDay");
				day.attr("data-date",dayToDraw);
				if(dayToDraw.toSQLDateString()>=today.toSQLDateString()&&(dayToDraw.getDay()>0&&dayToDraw.getDay()<6))day.click(callback);
				day.addClass(getClasses (today, dayToDraw, m));
				days.push (day);
			}
			calendarUL.append(days);
			builtMonths[m.getFullYear()+m.getMonth()] = calendarUL.html();
		}else {
			calendarUL.html (builtMonths[m.getFullYear()+"-"+m.getMonth()]);
			$('li.clickable').click(function(){alert($(this).data("sql_date"))});
		}
		//alert (new Date().getMilliseconds()-start);
	}
	
	calendar.drawDays(month);
	
	next.click(function(){
		drawnMonth = new Date(drawnMonth.getFullYear(), drawnMonth.getMonth()+1);
		calendar.drawDays (drawnMonth);
	});
	
	prev.click(function(){
		drawnMonth = new Date(drawnMonth.getFullYear(), drawnMonth.getMonth()-1);
		calendar.drawDays (drawnMonth);
	});

	function getClasses (today, toDraw, forMonth) {
		var classStr = "";
		classStr += toDraw.getMonth() == forMonth.getMonth()? "currentMonth ":"otherMonth ";
		classStr += today.toDateString() == toDraw.toDateString()? "today ":"";
		classStr += toDraw.toSQLDateString() >= today.toSQLDateString()&&(toDraw.getDay()>0&&toDraw.getDay()<6)?"clickable ":"";
		return classStr;
	}
	
	return calendar;
	
	function makeHeads (lang) {
		var initials_espanol = new Array ("D","L","M","M","J","V","S");
		var initials_english = new Array ("S","M","T","W","T","F","S");
		var initials_francais = new Array ("S","M","T","W","T","F","S");
		var lang = lang || "espanol";
		var ret = "";
		for (var i in eval ("initials_"+lang))ret+='<li class="calendarDay dayInitials">'+eval ("initials_"+lang)[i]+'</li>';
		return ret;
	}
	
	function isDate (date) {
		if (date.constructor === Date) {
			date = date.getMonth()?date:new Date();
			return date;
		}else if (date.constructor === String && new Date(date).getMonth()) {
			return new Date (date);
		}else if (date.constructor === Object) {
			for (var i in date) {
				if (isDate(date[i])) {
					return isDate(date [i]);
				};
			}
		}else {
			return null;
		}
	}
	
	function isLang (lang) {
		if (lang.constructor === String) {
			return available_langs.indexOf(lang)>-1?lang:null;
		}else if (lang.constructor === Object){
			for (i in lang) {
				if (isLang (lang[i])) {
					return lang[i];
				};
			}
		}
		return null;
	}
	
	function isElement (element) {
		if ($(element).get && $(element).get(0) && $(element).get(0).toString().search("HTML")>-1) return $(element);
		if (typeof element.constructor === String) {
			if ($(element).get && $(element).get(0) && $(element).get(0).toString().search("HTML")>-1) return $(element);
		}else if (element.constructor === Object) {
			for (i in element) {
				if (isElement(element[i])) {
					return isElement (element[i]);
				}
			}
		}
		return null;
	}
	
	function isFunction (fName) {
		if (typeof fName == "function") return fName;
		try {
			ret = eval (fName);
			return ret;
		}
		catch (e) {
			return null;
		}
	}
}