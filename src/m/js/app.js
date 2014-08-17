// Fantasy Healthcare Main App Logic
var FHC = FHC || {};
FHC.league = [];
FHC.teams = [];
FHC.teams.teams = [];
FHC.popup = [];
FHC.popup.draft = 0;
var timestamp = 0;
var timeleft = 0;

$(function() {

	// QUEUE
	$(".queue").click(function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'queue',
			tok: $(this).attr('data-tok'),
			do: 'back'
		},
		function(data) {
			if(data.success){
				$.mobile.loading('hide');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// SIGN UP BUTTON
	$("#btn-signup").click(function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'signup',
			f: $('#signup-firstname').val(),
			l: $('#signup-lastname').val(),
			e: $('#signup-email').val(),
			e2: $('#signup-emailagain').val(),
			p: $('#signup-password').val(),
			p2: $('#signup-passwordagain').val()
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				$.mobile.changePage('#login');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// LOGIN BUTTON
	$("#btn-login").click(function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'login',
			u: $('#login-email').val(),
			p: $('#login-password').val()
		},
		function(data) {
			//alert(data);
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				window.location = '#main';//'?tok='+data.success;
			} 
			if(data.error) {
				$.mobile.loading('hide');
				$('body').shake(6,10,500);
			}
		});
	});
	
	// LOGOUT BUTTON
	$("#btn-logout").click(function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'logout'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				location.reload();
			} 
			if(data.error) {
				$.mobile.loading('hide');
				$('body').shake(6,10,500);
			}
		});
	});
	
	// CREATE TEAM BUTTON
	$("#btn-createteam").click(function(evt) {
		playSound('click');
		evt.preventDefault();
		if($('#createteam-name').val() && $('#createteam-img').val()) {
			$('#form-createteam').submit();
		} else {
			alert("One of the fields is blank. Please fill them both in.");
		}
	});
	
	// CREATE LEAGUE BUTTON
	$(document).delegate("#btn-createleague", 'click', function() {
		playSound('click');
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'createleague',
			name: $('#createleague-name').val(),
			password: $('#createleague-password').val(),
			region: $('#createleague-region option:selected').val(),
			start: $('#createleague-start option:selected').val()
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				window.location = '#dashboard';//'?tok='+data.success;
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// SELECT LEAGUE
	$(document).delegate("#join li .passwordno", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'setleague',
			selected: $(this).attr('data-lid')
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				FHC.league = data;
				$("#invitefriends-comment").text("Who's up for a friendly game of Fantasy Healthcare? Join the league \""+FHC.league.name+"\" and see if you can build a better hospital!");
				$.mobile.loading('hide');
				$.mobile.changePage('#dashboard');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	$(document).delegate("#join li .passwordreq", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$('#join-lid').val($(this).attr('data-lid'));
		$('#join-password').val('');
		$.mobile.changePage("#joinpopup");
	});
	$(document).delegate("#joinwithpassword", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'setleague',
			selected: $('#join-lid').val(),
			password: $('#join-password').val()
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				FHC.league = data;
				$.mobile.loading('hide');
				$.mobile.changePage('#dashboard');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// JOIN POPUP - PAGE SHOW
	$(document).delegate('#joinpopup', 'pageshow', function () {
		$('#join-password').focus();
	});
			
	// JOIN - PAGE SHOW
	$(document).delegate('#join', 'pageshow', function () {
		$('#leaguelist').empty();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'getleagues'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				delete data.success;
				$.each(data, function(k,v) {
					thisclass = "passwordno";
					thisicon = 'arrow-r';
					if(k.slice(-1) == "*"){
						thisclass = "passwordreq";
						thisicon = 'lock';
					}
					$('#leaguelist').append('<li data-icon="'+thisicon+'"><a class="'+thisclass+'" data-lid="'+parseInt(k)+'" href="#">'+v+'</a></li>');
				});
				$('#leaguelist').listview('refresh');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				$('body').shake(6,10,500);
				alert(data.error);
			}
		});
	});
	
	// DASHBOARD - PAGE SHOW
	$(document).delegate('#dashboard', 'pageshow', function () {
		$.mobile.loading('show');
		$('#teamlist').empty();
		$.post("../api?json=1", {
			action: 'dashboard'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				getTeams();
				FHC.dashboard = data;
				$.mobile.loading('hide');
				if(data.owner){ // Stay on dashboard
					$('#draftnowcontainer').show();
				} else {
					$('#draftnowcontainer').hide();
				}
				if(data.success > 0){ // Stay on dashboard
					timestamp = (FHC.league.start * 1000) - Date.now();
					timestamp /= 1000;
				}
				if(data.success == '-1'){ // Not joined to a league
					$.mobile.changePage("#join");
				}
				if(data.success == '-2'){ // No team on league
					$.mobile.changePage("#createteam");
				}
				if(FHC.league.status == '2'){ // Draft in progress
					$.mobile.changePage("#draft", {transition:"fade"});
				}
				if(FHC.league.status == '3'){ // Game in progress
					$.mobile.changePage("#gamewait", {transition:"fade"});
				}
			}
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// DASHBOARD SIGNIN - PAGE SHOW
	$(document).delegate('#dashboard-signin', 'pageshow', function () {
		$.post("../api?json=1", { 
			action: 'rejoin'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				FHC.league = data;
				$("#invitefriends-comment").text("Who's up for a friendly game of Fantasy Healthcare? Join the league \""+FHC.league.name+"\" and see if you can build a better hospital!");
				$.mobile.changePage('#dashboard');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	
	
	// DRAFT - GET PLAYERS
	$(document).delegate('#draftpick', 'pageshow', function () {
		$.mobile.loading('show');
		$.post("../api?json=1", {
			action: 'getplayers',
			expertise: FHC.stack.param
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				FHC.draft = data;
				$('#draftexpertise').text(FHC.draft.expertise);
				$('#draftlist').empty();
				$.each(FHC.draft.players, function(k,v) {
					$('#draftlist').append('<li><a data-pid="'+v.pid+'" href="#">'+"Dr. "+v.lastname+' <span class="sm">('+v.provider+')</span></a></li>');
				});
				$('#draftlist').listview('refresh');
				$.mobile.loading('hide');
			}
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// DRAFT - GET STACK
	$(document).delegate('#draft', 'pageshow', function () {
		getStack();
	});
	
	// DRAFT PICK - PAGE SHOW
	$(document).delegate('#draftpick', 'pageshow', function () {
		if(!FHC.popup.draft){
			playSound('feedback');
			$("#popup-draftpick").popup("open");
			FHC.popup.draft = 1;
		}
	});
	
	// DRAFT PICK - POPUP CLOSE
	$(document).delegate("#popup-draftpick-close", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$("#popup-draftpick").popup("close");
	});
	
	// DRAFT - PICK
	$(document).delegate("#draftlist li a", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'setpick',
			stack: FHC.stack.success,
			selected: $(this).attr('data-pid')
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				$.mobile.changePage('#draft');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
				$.mobile.changePage('#draft');
			}
		});
	});
	
	// DRAFT PICK - PAGE SHOW
	$(document).delegate('#draftpick', 'pageshow', function () {
		timeleft = 30;
	});
	
	// LEAGUE - RESET
	$(document).delegate("#leaguereset", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'leaguereset'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				$.mobile.changePage('#join');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// LEAGUE - DELETE
	$(document).delegate("#leaguedelete", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'leaguedelete'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				$.mobile.changePage('#join');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// DRAFT - START
	$(document).delegate("#draftstart", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'draftstart'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				countdown = 0;
				$.mobile.loading('hide');
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
	// INVITE FRIENDS
	$(document).delegate("#btn-invitefriends", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'invitefriends'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				$.mobile.changePage('#dashboard');
			}
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
});

// TRIVIA - PAGE SHOW
	$(document).delegate('#trivia', 'pageshow', function () {
		$('#trivia-result').hide();
		$('#triviaquestions').empty();
		$.mobile.loading('show');
		$.post("../api?json=1", { 
			action: 'gettrivia'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				$.mobile.loading('hide');
				delete data.success;
				$.each(data, function(k,v) {
					html = '<div class="trivia" id="t-'+v.qid+'"><div class="trivia-q">'+k+'. '+v.q+'</div><div class="trivia-a"><label><input type="radio" name="'+v.qid+'" value="1">A) '+v.a1+'</label><br><label><input type="radio" name="'+v.qid+'" value="2">B) '+v.a2+'</label>';
					if(v.a3){
						html += '<br><label><input type="radio" name="'+v.qid+'" value="3">C) '+v.a3+'</label><br><label><input type="radio" name="'+v.qid+'" value="4">D) '+v.a4+'</label>';
					}
					html += '</div></div>';
					$('#triviaquestions').append(html);
				});
			} 
			if(data.error) {
				$.mobile.loading('hide');
				alert(data.error);
			}
		});
	});
	
// TRIVIA CHECK
	$(document).delegate("#btn-trivia-check", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		var answerNum = 0;
		var correct = 0;
		$.each($("#triviaquestions input"), function() {
			if($(this).is(':checked')){
				answerNum++;
			}
		});
		if(answerNum < 10) {
			alert('You forgot to answer a question or two. Please go back and fill them.');
		} else {
			$('#btn-trivia-check').button('disable');
			$.mobile.loading('show');
			$.post("../api?json=1", { 
				action: 'gettriviacheck',
				form: $('#triviaquestions').serializeArray()
			},
			function(data) {
				data = $.parseJSON(data);
				if(data.success){
					$.each($("#triviaquestions input"), function() {
						$(this).attr('disabled','disabled');
					});
					$.mobile.loading('hide');
					delete data.success;
					$.each(data, function(k,v) {
						if(v.answer == v.correct) {
							correct += 1;
							$('#t-'+k+' .trivia-q').append(' <span style="color:#0a0">CORRECT</span>');
						} else {
							$('#t-'+k+' .trivia-q').append(' <span style="color:#a00">INCORRECT</span>');
							$('#t-'+k+' .trivia-a input[value="'+v.answer+'"]').parent('label').addClass('wrong');
							$('#t-'+k+' .trivia-a input[value="'+v.correct+'"]').parent('label').addClass('right');
						}
					});
					$('#trivia-result').show();
					$('#trivia-result').html('You got <strong>'+correct+'</strong> questions correct. You have received <strong>'+correct+'</strong> points. <a href="#gamewait">Back to Game</a>');
					window.scrollTo(0,0);
					$('#btn-trivia-check').hide();
				} 
				if(data.error) {
					$.mobile.loading('hide');
					alert(data.error);
				}
			});
		}
	});

// SURVEY START
	$(document).delegate("#btn-survey", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.mobile.changePage('#gamewait');
	});

// PAPER FOOTBALL
	$(document).delegate("#pfb", 'click', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.post("../api?json=1", { 
			action: 'queue',
			tok: $(".queue").attr('data-tok'),
			do: 'pfb'
		});
	});
	$(document).delegate("h1", 'dblclick', function(evt) {
		playSound('click');
		evt.preventDefault();
		$.post("../api?json=1", { 
			action: 'queue',
			tok: $(".queue").attr('data-tok'),
			do: 'pfb'
		});
	});
/////////////// MISC. FUNCTIONS ///////////////

// DASHBOARD - GET TEAMS
	function getTeams() {
		$.post("../api?json=1", {
			action: 'getteams'
		},
		function(data) {
			data = $.parseJSON(data);
			if(data.success){
				FHC.teams = data;
				$('#teamlist').empty();
				if(FHC.teams.teams) {
					$.each(FHC.teams.teams, function(k,v) {
						$('#teamlist').append('<li>'+v+'</li>');
					});
				}
				$('#teamlist').listview('refresh');
			}
			if(data.status == 2){
				FHC.league.status = 2;
				$('#clock-draft').html("");
				$.mobile.changePage('#draft');
			}
			if(data.error) {
				alert(data.error);
			}
		});
	};

function getStack() {
	$.post("../api?json=1", {
		action: 'getstack'
	},
	function(data) {
		data = $.parseJSON(data);
		if(data.success > 0){
			FHC.stack = data;
			$('#draftexpertise').text('');
			$('#draftlist').empty();
			$.mobile.changePage("#draftpick", {transition:"fade"});
		}
		if(data.success == -2){
			$.mobile.changePage("#gamewait", {transition:"fade"});
		}
		if(data.error) {
			alert(data.error);
		}
	});
}

function getGameStatus() {
	$.post("../api?json=1", {
		action: 'getgamestatus'
	},
	function(data) {
		data = $.parseJSON(data);
		if(data.success > 0){
			FHC.game = data;
			$('#gamelog').empty();
			$.mobile.changePage("#game", {transition:"fade"});
			$('#tidh').html('<img src="../uploads/'+FHC.game.tidhlogo+'.jpg" alt="" />');
			$('#tidv').html('<img src="../uploads/'+FHC.game.tidvlogo+'.jpg" alt="" />');
			$('#tidhname').text(FHC.game.tidhname);
			$('#tidvname').text(FHC.game.tidvname);
			$('#hpts').text(pad(parseInt(FHC.game.hpt),2));
			$('#vpts').text(pad(parseInt(FHC.game.vpt),2));
		}
		if(data.success == -3){
			FHC.game = data;
			$('#gamelog').empty();
			if(FHC.game.owner) {
				$('#restartcontainer').show();
			} else {
				$('#restartcontainer').hide();
			}
			getFinal();
		}
		if(data.error) {
			alert(data.error);
		}
	});
}

function getGameLog() {
	$.post("../api?json=1", {
		action: 'getgamelog'
	},
	function(data) {
		data = $.parseJSON(data);
		if(data.success){
			if(JSON.stringify(FHC.gamelog || "") != JSON.stringify(data)){
				playSound('feedback');
			}
			// game has ended, kick player back to dashboard
			FHC.gamelog = data;
			if(FHC.gamelog.gid == 0) {
				//$.mobile.changePage("#dashboard");
			}
			$('#hpts').text(pad(parseInt(FHC.gamelog.hpt),2));
			$('#vpts').text(pad(parseInt(FHC.gamelog.vpt),2));
			$('#gamelog').empty();
				$.each(FHC.gamelog.logs, function(k,v) {
					$('#gamelog').append('<li data-glid="'+v.glid+'">'+v.note+'</li>');
				});
				var list = $('#gamelog');
				var listItems = list.children('li');
				list.append(listItems.get().reverse());
				$('#gamelog').listview('refresh');
		}
		if(data.error) {
			alert(data.error);
		}
	});
}

function getFinal() {
	$.post("../api?json=1", {
		action: 'getfinal'
	},
	function(data) {
		data = $.parseJSON(data);
		if(data.success){
			$('#resultwinner').html('<img src="../uploads/'+data.wlogo+'.jpg" style="vertical-align:middle;width:48px;height:48px;" alt=""> <strong>'+data.wname+'</strong> with <em>'+data.wwins+' wins!</em>');
			$('#resultlist').empty();
			$.each(data.games, function(k,v) {
				$('#resultlist').append('<div>'+v.note+'</div>');
			});
			$.mobile.changePage("#dashboardfinal");
			$('#resultlist').randomize('div');
			
		}
		if(data.error) {
			alert(data.error);
		}
	});
}

jQuery.fn.shake = function(intShakes, intDistance, intDuration) {
    this.each(function() {
        $(this).css("position","relative"); 
        for (var x=1; x<=intShakes; x++) {
        $(this).animate({left:(intDistance*-1)}, (intDuration/intShakes)/4)
    .animate({left:intDistance}, (intDuration/intShakes)/2)
    .animate({left:0}, (intDuration/intShakes)/4);
    }
  });
return this;
};

// Countdown
var d = new Date();
var timezone = (d.getTimezoneOffset() - 240) * 60 * 1000;

setInterval(function() {
	if(!$.mobile.activePage) {
		location.reload();
	}
	
	if($.mobile.activePage.attr("id") == "dashboard" && timestamp > 0){
		if(Date.now() < (FHC.league.start * 1000)){
			timestamp--;
			var days    = component(timestamp, 24 * 60 * 60),
				hours   = pad(component(timestamp,      60 * 60) % 24,2),
				minutes = pad(component(timestamp,           60) % 60,2),
				seconds = pad(component(timestamp,            1) % 60,2);
			$('#clock-draft').html("Draft will begin in "+ days + " days, <div class='clock'>" + hours + ":" + minutes + ":" + seconds + "</div>");
		}
		if((Date.now() >= (FHC.league.start * 1000)) && FHC.league.status < 2){
			FHC.league.status = 2;
			$('#clock-draft').html("");
			$.mobile.changePage("#draft");
		}
	}
	
	if($.mobile.activePage.attr("id") == "draftpick" && timeleft > 0){
		timeleft--;
		$('#clock-draft-left').text(timeleft);
		if(timeleft == 1) {
			var list = $("#draftlist li a").toArray();
			var elemlength = list.length;
			var randomnum = Math.floor(Math.random()*elemlength);
			var randomitem = list[randomnum];
			$('#draftlist').empty();
			$.mobile.loading('show');
			$.post("../api?json=1", { 
				action: 'setpick',
				stack: FHC.stack.success,
				selected: $(randomitem).attr('data-pid')
			},
			function(data) {
				data = $.parseJSON(data);
				if(data.success){
					$.mobile.loading('hide');
					$.mobile.changePage('#draft');
				} 
				if(data.error) {
					$.mobile.loading('hide');
					alert(data.error);
					$.mobile.changePage('#draft');
				}
			});
		}
	}
	
}, 1000);

function component(x, v) {
    return Math.floor(x / v);
}

function pad(n, width, z) {
  z = z || '0';
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

setInterval(function() {
	if($.mobile.activePage.attr("id") == "dashboard"){
		getTeams();
	}
	if($.mobile.activePage.attr("id") == "draft"){
		getStack();
	}
	if($.mobile.activePage.attr("id") == "gamewait"){
		getGameStatus();
	}
}, 7000);

setInterval(function() {
	if($.mobile.activePage.attr("id") == "game"){
		getGameLog();
	}
}, 10000);

$.fn.randomize = function(selector){
    (selector ? this.find(selector) : this).parent().each(function(){
        $(this).children(selector).sort(function(){
            return Math.random() - 0.5;
        }).detach().appendTo(this);
    });

    return this;
};

function playSound(file) {
	var snd = new Audio("snd/"+file+".mp3");
	snd.play();
}
function supportsAudio() {
	var a = document.createElement('audio'); 
	return !!(a.canPlayType && a.canPlayType('audio/mpeg;').replace(/no/, ''));
}
