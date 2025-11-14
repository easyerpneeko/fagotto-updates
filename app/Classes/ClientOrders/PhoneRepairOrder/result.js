$(document).ready(function () {
	var getUrlParameter = function getUrlParameter(sParam) {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)), sURLVariables = sPageURL.split('&'), sParameterName, i;
		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : sParameterName[1];
			}
		}
	};
	var classIcon = '';
	var classMsg = '';
	var lang = getUrlParameter('lang');
	var imeiNumber = getUrlParameter('imeiNumber');
	var estadoImei = getUrlParameter('estadoImei');
	var bandas = getUrlParameter('bandas');
	var baseOabi = getUrlParameter('baseOabi');
	var tipoInscripcion = getUrlParameter('tipoInscripcion');
	var fechaInscripcion = getUrlParameter('fechaInscripcion');
	var fechaVencimiento = getUrlParameter('fechaVencimiento');
	var codigo_company = getUrlParameter('comp');
	var codigo_motivo = getUrlParameter('motivo');
	var fecha_bloqueo = getUrlParameter('fechBl');
	var estado_bloqueo = getUrlParameter('estadoBl');
	var rdCode = getUrlParameter('rdCode');
	var rdMsg = getUrlParameter('rdMsg');
	var compatibilidad_bandas = ["2G", "3G", "4G", "3G e inferiores", "4G y 3G, sin soporte de 2G", "4G y 2G, sin soporte de 3G"];
	var imgIcon = 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/assets/img/radio.png';

	classIcon = 'icon-ui-info-rd';
	switch (rdCode) {
		case '101':
			classIcon = 'icon-ui-check-rd';
			classMsg = lang === 'us'
				? 'This equipment has a built-in FM radio tuner'
				: 'Este equipo <strong>tiene</strong> incorporado un sintonizador de radio FM';
			break;
		case '110':
			classIcon = 'icon-ui-not-rd';
			classMsg = lang === 'us'
				? 'This equipment does not have a built-in FM radio tunerr'
				: 'Este equipo <strong>no tiene</strong> incorporado un sintonizador de radio FM';
			break;
		case '111':
			classMsg = lang === 'us'
				? 'There is no FM radio tuner information for this equipment'
				: 'No hay información de sintonizador de radio FM para este equipo';
			break;
		case '006':
			classMsg = lang === 'us'
				? 'IMEI field with invalid format'
				: rdMsg;
			break;
		case '007':
			imgIcon = '';
			classMsg = lang === 'us'
				? 'IMEI entered not registered in OABI database'
				: rdMsg;
			break;
		case '999':
			imgIcon = '';
			classMsg = lang === 'us'
				? 'Internal service error. Please retry'
				: rdMsg;
			break;
		case '':
			classMsg = lang === 'us'
				? 'There is no FM radio tuner information for this equipment'
				: 'No hay información de sintonizador de radio FM para este equipo';
			break;
	}

	$('#radio_difusion').html('<img src="' + imgIcon + '"><i class="' + classIcon + ' icon-iu-attr "></i> ' + classMsg);

	if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
		$('#radio_difusion').html('');
		$('#radio_difusion').hide();
	}

	if (estadoImei == "ACTIVO" && bandas == "4G e inferiores" && baseOabi == "BDC") {
		//console.log("Tu equipo se encuentra inscrito en el sistema y puede operar en todas las redes móviles nacionales.");
		$('#texto_h3').html(
			lang === 'us'
				? 'Your device is registered in the system and can work in all national mobile networks.'
				: 'Tu equipo se encuentra inscrito en el sistema y puede operar en todas las redes móviles nacionales.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/ico_all_compatibilidad.png');
	} else if (estadoImei == "ACTIVO" && compatibilidad_bandas.indexOf(bandas) > -1 && baseOabi == "BDC") {
		//console.log("Tu equipo se encuentra inscrito en el sistema y puede operar en las redes móviles nacionales, según las restricciones que indica el sello.");
		$('#texto_h3').html(
			lang === 'us'
				? 'Your device is registered in the system and can operate in national mobile networks, according to the restrictions indicated by the seal.'
				: 'Tu equipo se encuentra inscrito en el sistema y puede operar en las redes móviles nacionales, según las restricciones que indica el sello.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		if (bandas == "2G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_3g_4g.png');
		} else if (bandas == "3G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_2g_4g.png');
		} else if (bandas == "4G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_2g_3g.png');
		} else if (bandas == "3G e inferiores") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_4g.png');
		} else if (bandas == "4G y 3G, sin soporte de 2G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_2g.png');
		} else if (bandas == "4G y 2G, sin soporte de 3G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_3g.png');
		}
	} else if (estadoImei == "INACTIVO" && bandas == "4G e inferiores" && baseOabi == "BDC") {
		$('#texto_h3').html(
			lang === 'us'
				? 'Your device is registered in the system and can work in all national mobile networks.'
				: 'Tu equipo se encuentra inscrito en el sistema y puede operar en todas las redes móviles nacionales.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		//$("#show_inactivo_info").text("Motivo de bloqueo: Robo - Hurto - Extravío");
		//$("#show_inactivo_info").show();
		//$("#show_date_inactivo_info").text("Fecha Bloqueo: -");
		//$("#show_date_inactivo_info").show();
		$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/ico_all_compatibilidad.png');
	} else if (estadoImei == "INACTIVO" && compatibilidad_bandas.indexOf(bandas) > -1 && baseOabi == "BDC") {
		$('#texto_h3').html(
			lang === 'us'
				? 'Your device is registered in the system and can operate in national mobile networks, according to the restrictions indicated by the seal.'
				: 'Tu equipo se encuentra inscrito en el sistema y puede operar en las redes móviles nacionales, según las restricciones que indica el sello.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		//$("#show_inactivo_info").text("Motivo de bloqueo: Robo - Hurto - Extravío");
		//$("#show_inactivo_info").show();
		//$("#show_date_inactivo_info").text("Fecha Bloqueo: -");
		//$("#show_date_inactivo_info").show();
		if (bandas == "2G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_3g_4g.png');
		} else if (bandas == "3G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_2g_4g.png');
		} else if (bandas == "4G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_2g_3g.png');
		} else if (bandas == "3G e inferiores") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_4g.png');
		} else if (bandas == "4G y 3G, sin soporte de 2G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_2g.png');
		} else if (bandas == "4G y 2G, sin soporte de 3G") {
			$("#extra-data").show();
			$("#imgBandas").attr('src', 'https://digital.clarochile.cl/wcm-iframe/consulta_imei/img/compatibilidad/no_3g.png');
		}
		/**/
	} else if (estadoImei == "ACTIVO" && baseOabi == "BDC" && tipoInscripcion == "Registro Otros Dispositivos") {
		//$("#icon_check").removeClass( "icon-ui-check" );
		//$("#icon_check").addClass( "icon-varios-cara-triste" );
		$('#texto_h3').html(
			lang === 'us'
				? 'Your device is registered in the system, but there is no information about whether it can operate in the mobile networks of all the companies in the country.'
				: 'Tu equipo se encuentra inscrito en el sistema, pero no hay información sobre si puede operar en las redes móviles de todas las empresas país.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	} else if (estadoImei == "INACTIVO" && baseOabi == "BDC" && tipoInscripcion == "Registro Otros Dispositivos") {
		//$("#icon_check").removeClass( "icon-ui-check" );
		//$("#icon_check").addClass( "icon-varios-cara-triste" );
		$('#texto_h3').html(
			lang === 'us'
				? 'Your device is registered in the system, but there is no information about whether it can operate in the mobile networks of all the companies in the country.'
				: 'Tu equipo se encuentra inscrito en el sistema, pero no hay información sobre si puede operar en las redes móviles de todas las empresas país.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		//$("#show_inactivo_info").text("Motivo de bloqueo: Robo - Hurto - Extravío");
		//$("#show_inactivo_info").show();
		//$("#show_date_inactivo_info").text("Fecha Bloqueo: -");
		//$("#show_date_inactivo_info").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	} else if (estadoImei == "ACTIVO" && baseOabi == "BDC" && tipoInscripcion == "Inscripción Administrativa") {
		//$("#icon_check").removeClass( "icon-ui-check" );
		//$("#icon_check").addClass( "icon-varios-cara-triste" );
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'Your device is registered in the system through Administrative registration, because it was purchased directly abroad, but there is no information on compatibility with SAE or if it can operate in the mobile networks of all companies in the country.'
				: 'Tu equipo celular se encuentra inscrito en el sistema a través de Inscripción Administrativa por haber sido adquirido directamente en el extranjero, pero no hay información sobre la compatibilidad con SAE o si puede operar en las redes móviles de todas las empresas del país.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	} else if (estadoImei == "INACTIVO" && baseOabi == "BDC" && tipoInscripcion == "Inscripción Administrativa") {
		//$("#icon_check").removeClass( "icon-ui-check" );
		//$("#icon_check").addClass( "icon-varios-cara-triste" );
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'Your device is registered in the system through Administrative registration, because it was purchased directly abroad, but there is no information on compatibility with SAE or if it can operate in the mobile networks of all companies in the country.'
				: 'Tu equipo celular se encuentra inscrito en el sistema a través de Inscripción Administrativa por haber sido adquirido directamente en el extranjero, pero no hay información sobre la compatibilidad con SAE o si puede operar en las redes móviles de todas las empresas del país.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		//$("#show_inactivo_info").text("Motivo de bloqueo: Robo - Hurto - Extravío");
		//$("#show_inactivo_info").show();
		//$("#show_date_inactivo_info").text("Fecha Bloqueo: -");
		//$("#show_date_inactivo_info").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	} else if (estadoImei == "ACTIVO" && baseOabi == "BDC" && tipoInscripcion == "Pre-Homologación") {
		//$("#icon_check").removeClass( "icon-ui-check" );
		//$("#icon_check").addClass( "icon-varios-cara-triste" );
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'This IMEI corresponds to a test device that is temporarily registered and can operate in national mobile networks only for 4 months from the date of registration.'
				: 'Se trata de un equipo de prueba que se encuentra inscrito temporalmente y puede operar en las redes móviles nacionales sólo por 4 meses a contar de la fecha de inscripción.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		$('#show_inscription_info').html(
			lang === 'us'
				? 'Date of inscription: ' + fechaInscripcion
				: 'Fecha de inscripción: ' + fechaInscripcion
		);
		//$("#show_inscription_info").text("Fecha de inscripción: "+fechaInscripcion);
		$("#show_inscription_info").show();

		// if(fechaVencimiento == ""){

		fecha_ins = new Date(fechaInscripcion + ' 23:00:00'); // Se le agregó la hora 23:00 para que complete el día con zona horaria distinta

		// Viejo código
		// sumadias= 121*24*60*60*1000;
		// fecha_new = fecha_ins.getTime()+(121*24*60*60*1000);
		// Viejo código

		// Nuevo Código
		fecha_ins.setMonth(fecha_ins.getMonth() + 4); // Sumar 4 meses a la fecha de inscripción
		// Nuevo Código

		fecha_new = new Date(fecha_ins); // Se crea nuevo objeto con la nueva fecha seteada

		formatMes = fecha_new.getMonth() + 1;
		if (formatMes.toString().length == 1) {
			formatMes = "0" + formatMes;
		}
		formatDia = fecha_new.getDate();
		if (formatDia.toString().length == 1) {
			formatDia = "0" + formatDia;
		}
		fecha_new = fecha_new.getFullYear() + "-" + formatMes + "-" + formatDia;
		fechaVencimiento = fecha_new;
		// }
		$('#show_vencimiento_info').html(
			lang === 'us'
				? 'Due date: ' + fechaVencimiento
				: 'Fecha de vencimiento: ' + fechaVencimiento
		);
		//$("#show_vencimiento_info").text("Fecha de vencimiento: "+fechaVencimiento);
		$("#show_vencimiento_info").show();
		var hoy = new Date().getTime();

		var date_inscripcion = new Date(fechaInscripcion).getTime();
		var date_vencimiento = new Date(fechaVencimiento + ' 23:00:00').getTime();  // Se le agregó la hora 23:00 para que complete el día con zona horaria distinta

		var diff = date_vencimiento - hoy;
		var restante = diff / (1000 * 60 * 60 * 24);
		if (restante < 0) {
			restante = 0;
		}
		$('#show_restante_info').html(
			lang === 'us'
				? 'Days remaining before Blocking: ' + parseInt(restante)
				: 'Días restantes antes del Bloqueo: ' + parseInt(restante)
		);
		//$("#show_restante_info").text("Días restantes antes del bloqueo: "+restante);
		$("#show_restante_info").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	} else if (estadoImei == "INACTIVO" && baseOabi == "BDC" && tipoInscripcion == "Pre-Homologación") {
		//$("#icon_check").removeClass( "icon-ui-check" );
		//$("#icon_check").addClass( "icon-varios-cara-triste" );
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'This IMEI corresponds to a test device that is temporarily registered and can operate in national mobile networks only for 4 months from the date of registration.'
				: 'Se trata de un equipo de prueba que se encuentra inscrito temporalmente y puede operar en las redes móviles nacionales sólo por 4 meses a contar de la fecha de inscripción.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();

		// if(fechaVencimiento == ""){

		fecha_ins = new Date(fechaInscripcion + ' 23:00:00'); // Se le agregó la hora 23:00 para que complete el día con zona horaria distinta

		// Viejo código
		// sumadias= 121*24*60*60*1000;
		// fecha_new = fecha_ins.getTime()+(121*24*60*60*1000);
		// Viejo código

		// Nuevo Código
		fecha_ins.setMonth(fecha_ins.getMonth() + 4); // Sumar 4 meses a la fecha de inscripción
		// Nuevo Código

		fecha_new = new Date(fecha_ins); // Se crea nuevo objeto con la nueva fecha seteada
		formatMes = fecha_new.getMonth() + 1;
		if (formatMes.toString().length == 1) {
			formatMes = "0" + formatMes;
		}
		formatDia = fecha_new.getDate();
		if (formatDia.toString().length == 1) {
			formatDia = "0" + formatDia;
		}
		fecha_new = fecha_new.getFullYear() + "-" + formatMes + "-" + formatDia;
		fechaVencimiento = fecha_new;
		// }
		$('#show_inscription_info').html(
			lang === 'us'
				? 'Date of inscription: ' + fechaInscripcion
				: 'Fecha de inscripción: ' + fechaInscripcion
		);
		//$("#show_inscription_info").text("Fecha de inscripción: "+fechaInscripcion);
		$("#show_inscription_info").show();
		$('#show_vencimiento_info').html(
			lang === 'us'
				? 'Due date: ' + fechaVencimiento
				: 'Fecha de vencimiento: ' + fechaVencimiento
		);
		//$("#show_vencimiento_info").text("Fecha de vencimiento: "+fechaVencimiento);
		$("#show_vencimiento_info").show();
		var hoy = new Date().getTime();
		var date_inscripcion = new Date(fechaInscripcion).getTime();
		var date_vencimiento = new Date(fechaVencimiento + ' 23:00:00').getTime();  // Se le agregó la hora 23:00 para que complete el día con zona horaria distinta
		var diff = date_vencimiento - hoy;
		var restante = diff / (1000 * 60 * 60 * 24);
		if (restante < 0) {
			restante = 0;
		}
		$('#show_restante_info').html(
			lang === 'us'
				? 'Days remaining before Blocking: ' + parseInt(restante)
				: 'Días restantes antes del Bloqueo: ' + parseInt(restante)
		);
		//$("#show_restante_info").text("Días restantes antes del bloqueo: "+restante);
		$("#show_restante_info").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	} else if (estadoImei == "ACTIVO" && baseOabi == "BDH") {
		//$("#icon_check").removeClass( "icon-ui-check" );
		//$("#icon_check").addClass( "icon-varios-cara-triste" );
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'Your device is registered in the system, for having trafficked before November 10, 2018, but there is no information about compatibility with SAE or if it can operate in the mobile networks of all the companies in the country.'
				: 'Tu equipo se encuentra inscrito en el sistema por haber cursado tráfico con anterioridad al 10/11/2018, pero no hay información sobre la compatibilidad con SAE (para los teléfonos), o si puede operar en las redes móviles de todas las empresas del páis.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	} else if (estadoImei == "INACTIVO" && baseOabi == "BDH") {
		//$("#icon_check").removeClass( "icon-ui-check" );
		//$("#icon_check").addClass( "icon-varios-cara-triste" );
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'Your device is registered in the system, for having trafficked before November 10, 2018, but there is no information about compatibility with SAE or if it can operate in the mobile networks of all the companies in the country.'
				: 'Tu equipo se encuentra inscrito en el sistema por haber cursado tráfico con anterioridad al 10/11/2018, pero no hay información sobre la compatibilidad con SAE (para los teléfonos), o si puede operar en las redes móviles de todas las empresas del páis.'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		//$("#show_inactivo_info").text("Motivo de bloqueo: Robo - Hurto - Extravío");
		//$("#show_inactivo_info").show();
		$("#show_date_inactivo_info").text("Fecha Bloqueo: -");
		$("#show_date_inactivo_info").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	} else if (estadoImei == "ESTADO_1" || estadoImei == "ESTADO_2" || estadoImei == "ESTADO_3" || estadoImei == "ESTADO_99" && baseOabi == "BDT") {
		$("#icon_check").removeClass("icon-ui-check");
		$("#icon_check").addClass("icon-varios-cara-triste");
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'Your device is not registered but can operate temporarily in national mobile networks for 30 days, starting from the date when you first inserted the Sim Card or chip of a national operator. If your device was brought from abroad, you must register it before the expiration date, or it will be blocked. Go to https://multibanda.cl/ia, to know the procedure. If your divice was purchased in Chile, it will be disabled for use in national networks after 30 days from the date you first inserted the chip. You can ask the company that sold it to you that, according to what is indicated in the Consumer Law, proceed to: - Money refund, or, - The change of divice by an approved one; and, - Eventually, compensation for damages (the latter in courts).'
				: 'Tu equipo no se encuentra inscrito pero puede operar temporalmente en las redes móviles nacionales por 30 días a contar de la fecha en que por primera vez insertó la Sim Card o chip de un operador nacional. Si tu equipo lo trajiste desde el extranjero, lo debes inscribir antes de la fecha de vencimiento, o será bloqueado. Ingresa a https://multibanda.cl/ia, para conocer el procedimiento. Si tu equipo fue adquirido en Chile, quedarpa inhabilitado para su uso en las redes nacionales transcurrido el plazo de 30 días contado desde la fecha que se insertó por primera vez el chip. Puedes solicitar a la empresa que te lo vendió que, de acuerdo a lo que indica la Ley del Consumidor, proceda a: - La devolución del dinero, o, - El cambio de equipo por uno homologado; y, - Eventualmente, indemnización de daños y perjuicios (esto último, en tribunales).'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
		$('#show_inscription_info').html(
			lang === 'us'
				? 'Temporary registration date: ' + fechaInscripcion
				: 'Fecha de inscripción temporal: ' + fechaInscripcion
		);
		//$("#show_inscription_info").text("Fecha de inscripción temporal: "+fechaInscripcion);

		fecha_ins = new Date(fechaInscripcion + ' 23:00:00'); // Se le agregó la hora 23:00 para que complete el día con zona horaria distinta

		// Viejo código
		// sumadias= 31*24*60*60*1000;
		// fecha_new = fecha_ins.getTime()+(31*24*60*60*1000);
		// Viejo código

		// Nuevo Código
		// fecha_ins.setMonth(fecha_ins.getMonth() + 4); // Sumar 4 meses a la fecha de inscripción
		fecha_ins.setDate(fecha_ins.getDate() + 30); // Suma 30 días a la fecha de inscripción
		// Nuevo Código

		fecha_new = new Date(fecha_ins); // Se crea nuevo objeto con la nueva fecha seteada
		formatMes = fecha_new.getMonth() + 1;
		if (formatMes.toString().length == 1) {
			formatMes = "0" + formatMes;
		}
		formatDia = fecha_new.getDate();
		if (formatDia.toString().length == 1) {
			formatDia = "0" + formatDia;
		}
		fecha_new = fecha_new.getFullYear() + "-" + formatMes + "-" + formatDia;
		$("#show_inscription_info").show();
		$('#show_vencimiento_info').html(
			lang === 'us'
				? 'Expiration Date: ' + fecha_new
				: 'Fecha de vencimiento: ' + fecha_new
		);
		//$("#show_vencimiento_info").text("Fecha de vencimiento: "+fecha_new);
		$("#show_vencimiento_info").show();
		var hoy = new Date().getTime();
		var date_vencimiento = new Date(fecha_new + ' 23:00:00').getTime();  // Se le agregó la hora 23:00 para que complete el día con zona horaria distinta
		var diff = date_vencimiento - hoy;
		var restante = diff / (1000 * 60 * 60 * 24);
		if (restante < 0) {
			restante = 0;
		}
		$('#show_restante_info').html(
			lang === 'us'
				? 'Days remaining before blocking: ' + parseInt(restante)
				: 'Días restantes antes del bloqueo: ' + parseInt(restante)
		);
		//$("#show_restante_info").text("Días restantes antes del bloqueo: "+parseInt(restante));
		$("#show_restante_info").show();
	} else if (estadoImei == "ESTADO_4" || estadoImei == "ESTADO_5" && baseOabi == "BDT") {
		$("#icon_check").removeClass("icon-ui-check");
		$("#icon_check").addClass("icon-varios-cara-triste");
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'Your device is not registered and the period of 30 days from the date on which you inserted the Sim Card or chip of a national operator fot the first time has expired, so it is blocked. If your device was brought from abroad, you must register using the instructions indicated in https://multibanda.cl/ia. If your divice was purchased in Chile, it will be permanently disabled for use in national networks. You can ask the company that sold it to you that, according to what is indicated in the Consumer Law, proceed to: - Money refund, or, - The change of equipment by an approved one; and, - Eventually, compensation for damages (the latter in courts).'
				: 'Tu equipo no se encuentra inscrito y ya expiró el periodo de 30 días a contar de la fecha en que por primera vez insertó la Sim Card o chip de un operador nacional, por lo que está bloqueado. Si tu equipo lo trajiste desde el extranjero, lo debes inscribir siguiendo las instrucciones que se indican en https://multibanda.cl/ia. Si tu equipo fue adquirido en Chile, quedará definitivamente inhabilitado para su uso en las redes nacionales. Puedes solicitar a la empresa que te lo vendió que, de acuerdo a lo que indica la Ley del Consumidor, proceda a: - La devolución del dinero, o, - El cambio de equipo por uno homologado; y, - Eventualmente, indemnización de daños y perjuicios (esto último, en tribunales).'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
		$('#show_inscription_info').html(
			lang === 'us'
				? 'Date of temporary registration: ' + fechaInscripcion
				: 'Fecha de inscripción temporal: ' + fechaInscripcion
		);
		//$("#show_inscription_info").text("Fecha de inscripción temporal: "+fechaInscripcion);
		$("#show_inscription_info").show();
		$('#show_vencimiento_info').html(
			lang === 'us'
				? 'Due date: ' + fechaVencimiento
				: 'Fecha de vencimiento: ' + fechaVencimiento
		);
		$("#show_vencimiento_info").show();

	} else if (baseOabi != "BDT" && baseOabi != "BDH" && baseOabi != "BDC" || estadoImei == "ESTADO_6") {
		$("#icon_check").removeClass("icon-ui-check");
		$("#icon_check").addClass("icon-varios-cara-triste");
		$('#texto_h3').html(
			lang === 'us'
				? '\n' +
				'Your device is not registered in the system and can not work in national mobile networks. If your device was brought from abroad, you must register it. Go to https://multibanda.cl/ia, to know the procedure. If your divice was purchased in Chile, and appears as not registered, it is because it is not approved and will be disabled for use in national networks after the deadline of 30 days from the date you insert a chip for the first time. You can ask the company that sold it to you that, according to what is indicated in the Consumer Law, proceed to: - Money refund, or, - The change of equipment by an approved one; and, - Eventually, compensation for damages (the latter in courts).'
				: 'Tu equipo no se encuentra inscrito en el sistema y no puede funcionar en las redes móviles nacionales. Si tu equipo lo trajiste desde el extranjero, lo debes inscribir. Ingresa a https://multibanda.cl/ia, para conocer el procedimiento. Si tu equipo fue adquirido en Chile y aparece como no inscrito, es porque no está homologado y quedará inhabilitado para su uso en las redes nacionales transcurrido el plazo de 30 días contado desde la fecha que insertes por primera vez un chip. Puedes solicitar a la empresa que te lo vendió que, de acuerdo a lo que indica la Ley del Consumidor, proceda a: - La devolución del dinero, o, - El cambio de equipo por uno homologado; y, - Eventualmente, indemnización de daños y perjuicios (esto último, en tribunales).'
		);
		if (codigo_motivo != "No se encontraron registros" && estado_bloqueo == "Equipo Bloqueado") {
			$('#texto_bloqueo').show();
			$('#texto_bloqueo').html(
				lang === 'us'
					? 'However, it is blocked by ' + codigo_motivo + ' , from ' + fecha_bloqueo + ' , by complaint at the company ' + codigo_company + '.'
					: 'No obstante, se encuentra  bloqueado por ' + codigo_motivo + ' , desde el ' + fecha_bloqueo + ' , por denuncia en la empresa ' + codigo_company + '.'
			);
		}
		$('#texto_detalle').html(
			lang === 'us'
				? 'All IMEIs of mobile devices, both cellhpones and other devices, must be registered in a centralized system in order to operate in national networks.'
				: 'Todos los IMEI de los equipos móviles, tanto teléfonos como otros dispositivos, deberán estar inscritos en un sistema centralizado para poder operar en las redes nacionales.'
		);
		$('#texto_sae').html(
			lang === 'us'
				? 'Multibanda SAE - See detailed information at www.multibanda.cl'
				: 'Multibanda SAE – Ver información detallada en www.multibanda.cl'
		);
		$('#texto_li').empty();
		$("#extra-data").show();
		$('#show_imei').html(
			lang === 'us'
				? 'IMEI Consulted: ' + imeiNumber
				: 'IMEI Consultado: ' + imeiNumber
		);
		//$("#show_imei").text("IMEI: "+imeiNumber);
		$("#show_imei").show();
		$("#info_bandas").remove();
		$("#imgBandas").remove();
	}
});
