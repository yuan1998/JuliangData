export const round = function (num, places) {
    return +(Math.round(num + "e+" + places) + "e-" + places);
}

export const datesRange = (start, end, format = 'YYYY-MM-DD') => {
    let startDate = moment(start);
    let endDate   = moment(end);
    console.log('startDate :', startDate);
    console.log('endDate :', endDate);
    let result = [];
    for (let m = moment(startDate) ; (m.isBefore(endDate) || m.isSame(endDate)) ; m.add(1, 'days')) {
        result.push(m.format(format));
    }
    console.log('result :', result);
    return result;
}
