import axiosClient from '../../src/api';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { PageComponent } from '../../src/types/PageComponent';
import { topicForm } from '../../src/components/topic-form';

export async function getServerSideProps(
  context: GetServerSidePropsContext,
): Promise<GetServerSidePropsResult<unknown>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { ...(await serverSideTranslations('de', ['common', 'topic'])), user },
  };
}

const CreateTopic: PageComponent<void> = topicForm(
  'headlineCreate',
  'buttonCreate',
  () => {
    // Nothing
  },
  async (router, values) => {
    const response = await axiosClient.post(`/topic`, values);
    router.push('/topic/' + response.data.id);
  },
);

CreateTopic.layout = 'main';
export default CreateTopic;
