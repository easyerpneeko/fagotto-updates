export function getterAplications(state) {
  return state.aplications;
}

export function getterPermisos(state) {
  return state.permisos;
}
export function getSelectAplications(state) {
  let apps = state.allAplications;
  let options = [];

  if(apps && apps.length > 0){
    apps.map((app) => {
      options.push({label: app.name, value: app.id})
    });
  }

  return options;
}

// Traer app de la store
export function getterApp(state) {
  return state.app;
}
// Traer usuarios de la app en la store
export function getterUsersApp(state) {
  return state.usersApp;
}
// Traer roles de usuarios de la app en la store
export function getterRolesOptions(state) {
  if(state.roles.length > 0){
    var arrayOptions = [];
    for (var i = 0; i < state.roles.length; i++) {
      arrayOptions.push({
        label: state.roles[i].name,
        value: state.roles[i].id,
      });
    }
    return arrayOptions;
  }
  return false;
}
export function getterRoles(state) {
  return state.roles;
}
