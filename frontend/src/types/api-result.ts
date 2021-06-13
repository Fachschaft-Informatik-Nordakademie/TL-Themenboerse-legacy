export type ApiResult<T> = {
  content: T[];
  total: number;
  pages: number;
  last: boolean;
  perPage: number;
};
