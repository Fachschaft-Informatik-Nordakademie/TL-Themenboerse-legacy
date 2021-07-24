import { User } from './user';

export type Application = {
  id: number;
  content: string;
  candidate: User;
  status: 'ACCEPTED' | 'REJECTED' | 'OPEN';
};
