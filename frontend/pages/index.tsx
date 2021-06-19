import React from 'react';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../src/server/fetchUser';
import { User } from '../src/types/user';
import { PageComponent } from '../src/types/PageComponent';

type Props = {
  user: User | null;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  return {
    redirect: {
      destination: user ? '/topic' : '/login',
      permanent: false,
    },
  };
}

const Home: PageComponent<void> = (): JSX.Element => {
  return <div />;
};

export default Home;
