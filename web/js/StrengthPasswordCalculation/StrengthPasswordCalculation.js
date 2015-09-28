(function($)
{
	$.fn.StrengthPasswordCalculation = function(options)
	{
		var defaults = {
			"language": "fr"
		};

		options = $.extend(defaults, options);
		
		var i = 0;

		this.each(function()
		{
			$(this).keyup(function() {
				var res = zxcvbn($(this).val(), []);
				
				var password = getJsonTranslate(options.language);

				switch(res.score){
						case 0:
							info = password.veryWeak;
							class_name = 'very_weak_pwd';
							break;
						case 1:
							info = password.veryWeak;
							class_name = 'very_weak_pwd';
							break;
						case 2:
							info = password.weak;
							class_name = 'weak_pwd';
							break;
						case 3:
							info = password.medium;
							class_name = 'medium_pwd';
							break;
						case 4:
							info = password.strong;
							class_name = 'strong_pwd';
							break;
					}
					
					var div_class = "calculate_password";

					if (!$(this).next("."+div_class).length) {
						var div = $( "<div></div>" ).insertAfter($(this));
						div.addClass(div_class);
						div.prepend(password.strenghtPassword + "<span class='bold strength'>-</span><br>" + password.timeCrackPassword + "<span class='bold cracked_time'>-</span>");
					}
					$(this).next().children("span.strength").text(info);
					
					var crackTime = toHHMMSS(res.crack_time);

					$(this).next().children("span.cracked_time").text((crackTime == "") ? "-" : crackTime);
					$(this).next().removeClass();
					$(this).next().addClass(div_class + " " + class_name);
			});

			function toHHMMSS(seconds) {
				// Milliseconds
				var timeArray = seconds.toString().split('.');
				var ms = 0;

				if(timeArray.length == 2)
				{
					ms = parseInt(timeArray[1]);
				}
				var sec_num = parseInt(seconds, 10);
				
				var intlNumber = new Intl.NumberFormat([options.language]);
				
				var hours   = Math.floor(sec_num / 3600);
				var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
				var seconds = sec_num - (hours * 3600) - (minutes * 60);

				var time = new Array();
				
				time.push((parseInt(hours) == 0) ? "" : intlNumber.format(hours) + 'h');
				time.push((parseInt(minutes) == 0) ? "" : minutes + 'min');
				time.push((parseInt(seconds) == 0) ? "" : seconds + 's');
				time.push((parseInt(ms) == 0) ? "" : ms + 'ms');

				return time.join(" ").trim();
			}
			
			function getJsonTranslate(language) {
				switch(language)
				{
					case "en":
						var password = {
						 "strenghtPassword": "Password strength: ",
						 "timeCrackPassword": "Time to crack password: ",
						 "veryWeak": "Very weak",
						 "weak": "Weak",
						 "medium": "Medium",
						 "strong": "Strong"
						};
						break;
					case "es":
						var password = {
						 "strenghtPassword": "Seguridad de la contrase\u00F1a: ",
						 "timeCrackPassword": "Tiempo para romper la contrase\u00F1a: ",
						 "veryWeak": "Muy d\u00E9bil",
						 "weak": "D\u00E9bil",
						 "medium": "Media",
						 "strong": "Fuerte"
						};
						break;
					default:
						var password = {
						 "strenghtPassword": "Force du mot de passe : ",
						 "timeCrackPassword": "Temps pour cracker le mot de passe : ",
						 "veryWeak": "Tr\u00E8s faible",
						 "weak": "Faible",
						 "medium": "Moyen",
						 "strong": "Fort"
						};
				}

				return password;
			}
		});

		return this;
	};
})(jQuery)