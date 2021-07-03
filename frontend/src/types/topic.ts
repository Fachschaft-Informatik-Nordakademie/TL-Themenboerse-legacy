import { User } from './user';

export type Topic = {
  id: number;
  title: string;
  description: string;
  requirements: string;
  website?: string;
  tags: string[];
  scope: string;
  start?: string;
  deadline?: string;
  pages?: number;
  status: 'OPEN' | 'ASSIGNED' | 'LOCKED';
  author: User;
};
