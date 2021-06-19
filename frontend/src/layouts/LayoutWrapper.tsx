import DefaultLayout from './DefaultLayout';
import AuthLayout from './AuthLayout';
import MainLayout from './MainLayout';
import { PageComponent } from '../types/PageComponent';

export const layouts = {
  default: DefaultLayout,
  auth: AuthLayout,
  main: MainLayout,
};

type Props = {
  children: React.ReactElement<unknown, PageComponent<unknown>>;
};

const LayoutWrapper = (props: Props): JSX.Element => {
  // to get the text value of the assigned layout of each component
  const Layout = layouts[props.children.type.layout];
  // if we have a registered layout render children with said layout
  if (Layout != null) {
    return <Layout {...props}>{props.children}</Layout>;
  }
  // if not render children with fragment
  return <DefaultLayout {...props}>{props.children}</DefaultLayout>;
};

export default LayoutWrapper;
