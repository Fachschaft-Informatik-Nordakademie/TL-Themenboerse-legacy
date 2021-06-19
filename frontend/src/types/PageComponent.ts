import { layouts } from '../layouts/LayoutWrapper';

export type PageLayout = keyof typeof layouts;

export type PageComponent<Props> = ((props: Props) => JSX.Element) & {
  layout?: PageLayout;
};
