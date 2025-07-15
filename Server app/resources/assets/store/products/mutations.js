export function setProperty (state, params) {
    const key = params.key;
    const data = params.data;
    state[key] =  data;
  }
  