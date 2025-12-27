const fs = require('fs');
const path = require('path');
const axios = require('axios');

/**
 * Sistema de Asistencia - Helper para Electron
 */
class AsistenciaHelper {
  constructor() {
    this.config = this.loadConfig();
  }

  /**
   * Cargar configuración desde .env
   */
  loadConfig() {
    const envPath = path.join(__dirname, '../../.env');
    const config = {
      appId: 'AGU001',
      localNombre: 'Local',
      localLat: -33.4372,
      localLng: -70.6506,
      localRadius: 50,
      dbHost: 'localhost',
      dbName: 'asistencias',
      dbUser: 'root',
      dbPass: '',
      apiUrl: 'https://asistencia.fagottoerp.cl'
    };

    if (fs.existsSync(envPath)) {
      const envContent = fs.readFileSync(envPath, 'utf-8');
      const envLines = envContent.split('\n');

      envLines.forEach(line => {
        const [key, value] = line.split('=').map(s => s.trim());
        
        if (key === 'APP_ID') config.appId = value;
        if (key === 'LOCAL_NOMBRE') config.localNombre = value;
        if (key === 'LOCAL_LAT') config.localLat = parseFloat(value);
        if (key === 'LOCAL_LNG') config.localLng = parseFloat(value);
        if (key === 'LOCAL_RADIUS') config.localRadius = parseInt(value);
        if (key === 'DB_HOST') config.dbHost = value;
        if (key === 'DB_NAME') config.dbName = value;
        if (key === 'DB_USER') config.dbUser = value;
        if (key === 'DB_PASS') config.dbPass = value;
      });
    }

    return config;
  }

  /**
   * Generar sesión de check-in
   */
  async crearSesion() {
    const timestamp = Date.now();
    const sessionId = `SES${timestamp}${Math.random().toString(36).substring(2, 7)}`.toUpperCase();

    const sessionData = {
      session_id: sessionId,
      app_id: this.config.appId,
      expires_at: new Date(timestamp + 300000).toISOString().slice(0, 19).replace('T', ' '), // +5 min
      used: 0
    };

    // Guardar en DB remota
    try {
      await axios.post(`${this.config.apiUrl}/api/create-session.php`, sessionData);
    } catch (error) {
      console.error('Error creando sesión:', error.message);
    }

    return {
      sessionId,
      qrUrl: `https://asistencia.fagottoerp.cl/qrcheck.php?session=${sessionId}&appid=${this.config.appId}`,
      localNombre: this.config.localNombre
    };
  }

  /**
   * Consultar resultado de sesión
   */
  async consultarSesion(sessionId) {
    try {
      const response = await axios.get(`${this.config.apiUrl}/api/check-session.php?session=${sessionId}`);
      return response.data;
    } catch (error) {
      console.error('Error consultando sesión:', error.message);
      return null;
    }
  }

  /**
   * Obtener últimos registros del local
   */
  async obtenerUltimosRegistros(limit = 5) {
    try {
      const response = await axios.get(`${this.config.apiUrl}/api/ultimos-registros.php?appid=${this.config.appId}&limit=${limit}`);
      return response.data;
    } catch (error) {
      console.error('Error obteniendo registros:', error.message);
      return [];
    }
  }
}

module.exports = new AsistenciaHelper();
