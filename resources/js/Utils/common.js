export const round = function (num, places) {
    return +(Math.round(num + "e+" + places) + "e-" + places);
}
