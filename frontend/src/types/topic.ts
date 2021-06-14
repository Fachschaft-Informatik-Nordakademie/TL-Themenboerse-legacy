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
  status: string;
};
