
export const Fee = {
  appRate: 0.05,
  calcApp(amount) {
    const fee = amount * this.appRate;
    return Math.round(fee * 100) / 100;
  }
};
