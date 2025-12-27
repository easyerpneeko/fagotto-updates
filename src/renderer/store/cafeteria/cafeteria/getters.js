export function getCafeteria(state) {
  return state.cafeteria;
}
export function getBoard(state) {
  return state.board;
}
export function getOrder(state) {
  return state.order;
}
export function getAllOrdersActives(state) {
  return state.ordersActives;
}
export function getOnlyWaiter(state) {
  return state.onlyWaiter;
}
export function getKeepModalOpen(state) {
  return state.keepModalOpen;
}
export function getBoardsFree(state) {
  var boardsFree = [];
  if(state.cafeteria.length > 0){
    state.cafeteria.map((board)=>{
      if(board.state != "ocupada" || !board.order){
        boardsFree.push(board)
      }
    });
  }
  return boardsFree;
}
